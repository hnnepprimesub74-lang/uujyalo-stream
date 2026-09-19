<?php

namespace App\Filament\Support;

use App\Mail\PaymentProofApprovedMail;
use App\Models\Subscription;
use App\Services\RechargeService;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Mail;

class SubscriptionTableActions
{
    public const RECHARGE_GRACE_DAYS = 7;

    public static function details(): Action
    {
        return Action::make('details')
            ->label('Details')
            ->icon('heroicon-o-eye')
            ->color('gray')
            ->modalHeading(fn (Subscription $record) => "Order Details — {$record->user->name}")
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close')
            ->infolist([
                Section::make('Customer')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('user.name')->label('Name'),
                        TextEntry::make('user.phone')->label('Phone'),
                        TextEntry::make('user.email')->label('Email'),
                    ]),
                Section::make('Order')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('order_number')->label('Order ID')->copyable(),
                        TextEntry::make('plan.product.name')->label('Product'),
                        TextEntry::make('plan.full_name')->label('Plan'),
                        TextEntry::make('saleSource.name')->label('Source')->default('—'),
                        TextEntry::make('status')->label('Status')->badge(),
                        TextEntry::make('amount')->label('Amount Paid')->money('NPR'),
                        TextEntry::make('credit_due')->label('Credit Due')->money('NPR')
                            ->color(fn (Subscription $record) => $record->credit_due > 0 ? 'danger' : null),
                    ]),
                Section::make('Account Credentials')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('display_account_email')
                            ->label('Account Email')
                            ->getStateUsing(fn (Subscription $record) => $record->displayAccountEmail())
                            ->copyable(),
                        TextEntry::make('display_account_password')
                            ->label('Account Password')
                            ->getStateUsing(fn (Subscription $record) => $record->displayAccountPassword())
                            ->copyable(),
                        TextEntry::make('sharedAccount.max_slots')
                            ->label('Shared With')
                            ->getStateUsing(fn (Subscription $record) => $record->sharedAccount
                                ? "{$record->sharedAccount->usedSlots()} of {$record->sharedAccount->max_slots} customers on this account"
                                : null)
                            ->visible(fn (Subscription $record) => $record->sharedAccount !== null),
                    ]),
                Section::make('Recharge Tracking')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('starts_at')->label('Starts At')->date('M j, Y'),
                        TextEntry::make('expires_at')->label('Final Expiry')->date('M j, Y'),
                        TextEntry::make('total_days')->label('Total Days'),
                        TextEntry::make('days_recharged')->label('Days Recharged So Far'),
                        TextEntry::make('remaining_recharge_days')
                            ->label('Days Until Next Recharge')
                            ->getStateUsing(fn (Subscription $record) => $record->remainingRechargeDays() ?? 'Fully covered'),
                        TextEntry::make('next_recharge_date')->label('Next Recharge Due')->date('M j, Y')->placeholder('—'),
                    ]),
                Section::make('Recharge History')
                    ->schema([
                        RepeatableEntry::make('rechargeLogs')
                            ->label('')
                            ->columns(3)
                            ->schema([
                                TextEntry::make('recharged_at')->label('Date')->date('M j, Y'),
                                TextEntry::make('amount')->label('Amount')->money('USD'),
                                TextEntry::make('days_added')->label('Days Added'),
                            ]),
                    ])
                    ->visible(fn (Subscription $record) => $record->rechargeLogs()->exists()),
                Section::make('Admin Note')
                    ->schema([
                        TextEntry::make('admin_note')->label('')->default('—'),
                    ])
                    ->visible(fn (Subscription $record) => filled($record->admin_note)),
            ]);
    }

    public static function approve(): Action
    {
        return Action::make('approve')
            ->label('Approve & Activate')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->visible(fn (Subscription $record) => $record->status === Subscription::STATUS_PENDING)
            ->form([
                TextInput::make('total_days')
                    ->label('Total Days Purchased')
                    ->numeric()
                    ->required(),
            ])
            ->fillForm(fn (Subscription $record) => [
                'total_days' => $record->total_days ?? $record->plan->duration_days,
            ])
            ->requiresConfirmation()
            ->action(function (Subscription $record, array $data) {
                $totalDays = (int) $data['total_days'];
                $startsAt = now();
                $expiresAt = $startsAt->copy()->addDays($totalDays);
                $isSharedAccount = (bool) $record->plan?->product?->is_shared_account;

                $record->update([
                    'status' => Subscription::STATUS_ACTIVE,
                    'starts_at' => $startsAt,
                    'expires_at' => $expiresAt,
                    'total_days' => $totalDays,
                    'days_recharged' => $isSharedAccount ? $totalDays : 0,
                    'next_recharge_date' => $isSharedAccount ? null : $startsAt->toDateString(),
                    'expiring_reminder_sent' => false,
                    'expired_reminder_sent' => false,
                ]);

                $record->paymentProofs()->where('status', 'pending')->update([
                    'status' => 'approved',
                    'reviewed_by' => auth()->id(),
                    'reviewed_at' => now(),
                ]);

                Mail::to($record->user->email)->send(new PaymentProofApprovedMail($record));
            });
    }

    public static function addRecharge(): Action
    {
        return Action::make('addRecharge')
            ->label('Add Recharge')
            ->icon('heroicon-o-bolt')
            ->color('warning')
            ->visible(fn (Subscription $record) => $record->status === Subscription::STATUS_ACTIVE && $record->remainingDaysToCover() > 0)
            ->form([
                TextInput::make('amount')
                    ->label('Amount Added ($)')
                    ->numeric()
                    ->required()
                    ->helperText(fn (Subscription $record) => 'Plan cost: $'.number_format($record->plan->monthly_cost ?? 0, 2).'/month'),
            ])
            ->requiresConfirmation()
            ->action(function (Subscription $record, array $data, RechargeService $rechargeService) {
                $rechargeService->applyRecharge($record, (float) $data['amount']);
            });
    }

    public static function reactivateAndRecharge(): Action
    {
        return Action::make('reactivateAndRecharge')
            ->label('Reactivate & Recharge')
            ->icon('heroicon-o-bolt')
            ->color('warning')
            ->visible(fn (Subscription $record) => $record->remainingDaysToCover() > 0)
            ->form([
                TextInput::make('amount')
                    ->label('Amount Added ($)')
                    ->numeric()
                    ->required()
                    ->helperText(fn (Subscription $record) => 'Plan cost: $'.number_format($record->plan->monthly_cost ?? 0, 2).'/month'),
            ])
            ->requiresConfirmation()
            ->action(function (Subscription $record, array $data, RechargeService $rechargeService) {
                if ($record->status !== Subscription::STATUS_ACTIVE) {
                    $record->update(['status' => Subscription::STATUS_ACTIVE]);
                }

                $rechargeService->applyRecharge($record, (float) $data['amount']);
            });
    }

    public static function extend(): Action
    {
        return Action::make('extend')
            ->label('Extend 30 days')
            ->icon('heroicon-o-clock')
            ->color('info')
            ->visible(fn (Subscription $record) => $record->status === Subscription::STATUS_ACTIVE)
            ->requiresConfirmation()
            ->action(function (Subscription $record) {
                $base = $record->expires_at && $record->expires_at->isFuture() ? $record->expires_at : now();
                $record->update([
                    'expires_at' => $base->copy()->addDays(30),
                    'total_days' => $record->total_days + 30,
                    'expiring_reminder_sent' => false,
                    'expired_reminder_sent' => false,
                ]);
            });
    }

    public static function cancel(): Action
    {
        return Action::make('cancel')
            ->label('Cancel')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->visible(fn (Subscription $record) => in_array($record->status, [Subscription::STATUS_ACTIVE, Subscription::STATUS_PENDING, Subscription::STATUS_EXPIRED]))
            ->requiresConfirmation()
            ->action(fn (Subscription $record) => $record->update(['status' => Subscription::STATUS_CANCELLED]));
    }
}
