<?php

namespace App\Filament\Resources;

use App\Filament\Pages\AddOrder;
use App\Filament\Resources\PaymentProofResource\Pages;
use App\Mail\AccountReadyMail;
use App\Mail\PaymentProofApprovedMail;
use App\Mail\PaymentProofNeedsInfoMail;
use App\Mail\PaymentProofRejectedMail;
use App\Models\PaymentProof;
use App\Models\SharedAccount;
use App\Models\Subscription;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class PaymentProofResource extends Resource
{
    protected static ?string $model = PaymentProof::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Pending Approvals';

    protected static ?string $modelLabel = 'payment proof';

    protected static ?string $pluralModelLabel = 'Pending Approvals';

    protected static ?string $navigationGroup = 'Subscriptions';

    protected static ?int $navigationSort = 11;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Textarea::make('note')->columnSpanFull(),
        ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = PaymentProof::where('status', PaymentProof::STATUS_PENDING)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query
                ->with('subscription.plan.product')
                // Only the latest proof per order — once a customer resubmits,
                // the earlier attempt (rejected/needs_info) is superseded and
                // shouldn't keep cluttering the list as a separate row.
                ->whereIn('id', function ($subQuery) {
                    $subQuery->selectRaw('MAX(id)')
                        ->from('payment_proofs')
                        ->groupBy('subscription_id');
                }))
            ->columns([
                TextColumn::make('user.name')->searchable()->sortable(),
                TextColumn::make('user.phone')->label('Phone')->searchable(),
                TextColumn::make('subscription.plan.full_name')->label('Plan')->wrap(),
                TextColumn::make('subscription.amount')->label('Amount')->money('NPR'),
                TextColumn::make('reference_no')->label('Ref. No'),
                ImageColumn::make('screenshot_path')
                    ->label('Proof')
                    ->disk('public')
                    ->size(40)
                    ->action(Action::make('viewDetails'))
                    ->extraImgAttributes(['class' => 'cursor-zoom-in']),
                BadgeColumn::make('status')->colors([
                    'warning' => PaymentProof::STATUS_PENDING,
                    'success' => PaymentProof::STATUS_APPROVED,
                    'danger' => PaymentProof::STATUS_REJECTED,
                    'info' => PaymentProof::STATUS_NEEDS_INFO,
                ]),
                TextColumn::make('created_at')->label('Created')->dateTime('M j, g:i A')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options([
                    PaymentProof::STATUS_PENDING => 'Pending',
                    PaymentProof::STATUS_NEEDS_INFO => 'Needs Info',
                    PaymentProof::STATUS_APPROVED => 'Approved',
                    PaymentProof::STATUS_REJECTED => 'Rejected',
                ]),
            ])
            ->actions([
                Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (PaymentProof $record) => $record->status === PaymentProof::STATUS_PENDING)
                    ->form([
                        \Filament\Forms\Components\TextInput::make('reference_no')
                            ->label('Reference / Transaction No.')
                            ->required()
                            ->helperText('Check the screenshot and fill this in if the customer left it blank — required before approving.'),
                        \Filament\Forms\Components\TextInput::make('total_days')
                            ->label('Total Days Purchased')
                            ->numeric()
                            ->required(),
                    ])
                    ->fillForm(fn (PaymentProof $record) => [
                        'reference_no' => $record->reference_no,
                        'total_days' => $record->subscription->total_days ?? $record->subscription->plan->duration_days,
                    ])
                    ->requiresConfirmation()
                    ->action(function (PaymentProof $record, array $data) {
                        $record->update([
                            'status' => PaymentProof::STATUS_APPROVED,
                            'reference_no' => $data['reference_no'],
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);

                        $subscription = $record->subscription;
                        $plan = $subscription->plan;
                        $isSharedAccount = (bool) $plan->product?->is_shared_account;
                        $totalDays = (int) $data['total_days'];

                        // Payment is confirmed, but the countdown only starts once an
                        // account (shared slot or private credentials) is actually
                        // assigned — see below and the "Create Order" flow.
                        $subscription->update([
                            'status' => Subscription::STATUS_ACTIVE,
                            'total_days' => $totalDays,
                            'expiring_reminder_sent' => false,
                            'expired_reminder_sent' => false,
                        ]);

                        $accountAssignedNow = false;

                        if ($isSharedAccount) {
                            $slotsUsed = $plan->device_slots ?? 1;

                            $account = SharedAccount::where('product_id', $plan->product_id)
                                ->where('is_active', true)
                                ->get()
                                ->first(fn (SharedAccount $candidate) => $candidate->availableSlots() >= $slotsUsed);

                            if ($account) {
                                $startsAt = now();

                                $subscription->update([
                                    'shared_account_id' => $account->id,
                                    'slots_used' => $slotsUsed,
                                    'starts_at' => $startsAt,
                                    'expires_at' => $startsAt->copy()->addDays($totalDays),
                                    'days_recharged' => $totalDays,
                                    'next_recharge_date' => null,
                                ]);

                                $accountAssignedNow = true;
                            } else {
                                $subscription->update(['slots_used' => $slotsUsed]);

                                Notification::make()
                                    ->title('Approved, but no account has free slots yet')
                                    ->body('This customer has been placed on the Reassign page — they\'ll be assigned automatically (and their countdown will start then) once you add stock.')
                                    ->warning()
                                    ->send();
                            }
                        }

                        Mail::to($subscription->user->email)->send(new PaymentProofApprovedMail($subscription));

                        if ($accountAssignedNow) {
                            Mail::to($subscription->user->email)->send(new AccountReadyMail($subscription));
                        }

                        if (! $isSharedAccount) {
                            Notification::make()
                                ->title('Approved — now create the order')
                                ->body('The customer\'s email/password from checkout is prefilled. Confirm the details to activate the countdown.')
                                ->success()
                                ->send();

                            return redirect(AddOrder::getUrl(['subscriptionId' => $subscription->id]));
                        }
                    }),
                \Filament\Tables\Actions\ActionGroup::make([
                Action::make('viewDetails')
                    ->label('View Details')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading(fn (PaymentProof $record) => "Payment Proof — {$record->user->name}")
                    ->modalSubmitActionLabel('Save Reference No')
                    ->modalCancelActionLabel('Close')
                    ->modalWidth('4xl')
                    ->form([
                        Placeholder::make('summary')
                            ->label('')
                            ->content(function (PaymentProof $record) {
                                $rows = [
                                    'Customer' => $record->user->name,
                                    'Phone' => $record->user->phone,
                                    'Email' => $record->user->email,
                                    'Product' => $record->subscription->plan->product?->name,
                                    'Plan' => $record->subscription->plan->full_name,
                                    'Amount' => 'NPR '.number_format($record->subscription->amount, 2),
                                    'Payment Method' => $record->payment_method,
                                    'Status' => $record->status,
                                    'Submitted At' => $record->created_at->format('M j, Y g:i A'),
                                ];

                                $html = '<div class="grid grid-cols-2 sm:grid-cols-3 gap-x-6 gap-y-3 text-sm rounded-lg bg-gray-50 dark:bg-white/5 p-4">';

                                foreach ($rows as $label => $value) {
                                    $html .= '<div><div class="text-xs text-gray-500 dark:text-gray-400">'.e($label).'</div>'
                                        .'<div class="font-medium text-gray-950 dark:text-white">'.e($value ?? '—').'</div></div>';
                                }

                                $html .= '</div>';

                                return new HtmlString($html);
                            }),
                        TextInput::make('reference_no')
                            ->label('Reference / Transaction No.')
                            ->helperText('Check the screenshot(s) below and fill this in if the customer left it blank.'),
                        Placeholder::make('screenshots')
                            ->label('')
                            ->content(function (PaymentProof $record) {
                                $paths = array_filter([$record->screenshot_path, $record->screenshot_path_2]);

                                $html = '<div class="flex flex-wrap gap-4">';

                                foreach ($paths as $i => $path) {
                                    $url = Storage::disk('public')->url($path);
                                    $html .= '<a href="'.e($url).'" target="_blank" rel="noopener" class="block">'
                                        .'<div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Screenshot '.($i + 1).' — click to enlarge</div>'
                                        .'<img src="'.e($url).'" style="max-height:500px" class="w-auto rounded-lg border border-gray-300 dark:border-gray-700 cursor-zoom-in object-contain" />'
                                        .'</a>';
                                }

                                $html .= '</div>';

                                return new HtmlString($html);
                            }),
                        Placeholder::make('customer_note')
                            ->label('Note from Customer')
                            ->content(fn (PaymentProof $record) => $record->customer_note ?: '—')
                            ->visible(fn (PaymentProof $record) => filled($record->customer_note)),
                        Placeholder::make('note')
                            ->label('Admin Note')
                            ->content(fn (PaymentProof $record) => $record->note ?: '—')
                            ->visible(fn (PaymentProof $record) => filled($record->note)),
                    ])
                    ->fillForm(fn (PaymentProof $record) => [
                        'reference_no' => $record->reference_no,
                    ])
                    ->action(function (PaymentProof $record, array $data) {
                        $record->update(['reference_no' => $data['reference_no']]);

                        Notification::make()
                            ->title('Reference number saved')
                            ->success()
                            ->send();
                    }),
                Action::make('requestInfo')
                    ->label('Request Info')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('warning')
                    ->visible(fn (PaymentProof $record) => $record->status === PaymentProof::STATUS_PENDING)
                    ->form([
                        Textarea::make('note')
                            ->label('What do you need from the customer?')
                            ->helperText('e.g. "Screenshot is blurry, please retake it" or "Reference number doesn\'t match — double check and resubmit."')
                            ->required(),
                    ])
                    ->requiresConfirmation()
                    ->modalDescription('The customer will be emailed this note and asked to resubmit their payment proof. Their order stays pending — this does not reject it.')
                    ->action(function (PaymentProof $record, array $data) {
                        $record->update([
                            'status' => PaymentProof::STATUS_NEEDS_INFO,
                            'note' => $data['note'],
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);

                        Mail::to($record->user->email)->send(new PaymentProofNeedsInfoMail($record));

                        Notification::make()
                            ->title('Customer notified — waiting for them to resubmit')
                            ->success()
                            ->send();
                    }),
                Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (PaymentProof $record) => $record->status === PaymentProof::STATUS_PENDING)
                    ->form([
                        Textarea::make('note')->label('Reason for rejection')->required(),
                    ])
                    ->requiresConfirmation()
                    ->action(function (PaymentProof $record, array $data) {
                        $record->update([
                            'status' => PaymentProof::STATUS_REJECTED,
                            'note' => $data['note'],
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);

                        $record->subscription->update(['status' => Subscription::STATUS_REJECTED]);

                        Mail::to($record->user->email)->send(new PaymentProofRejectedMail($record));
                    }),
                ])
                    ->label('Actions')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentProofs::route('/'),
            'edit' => Pages\EditPaymentProof::route('/{record}/edit'),
        ];
    }
}
