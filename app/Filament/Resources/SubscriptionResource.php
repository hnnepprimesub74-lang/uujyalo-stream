<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionResource\Pages;
use App\Filament\Support\SubscriptionTableActions;
use App\Models\Plan;
use App\Models\SharedAccount;
use App\Models\Subscription;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'All Orders';

    protected static ?string $modelLabel = 'order';

    protected static ?string $pluralModelLabel = 'orders';

    protected static ?string $navigationGroup = 'Subscriptions';

    protected static ?int $navigationSort = 5;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('user_id')
                ->label('Customer')
                ->relationship('user', 'name')
                ->searchable()
                ->required(),
            Select::make('plan_id')
                ->label('Product Plan')
                ->relationship('plan', 'name')
                ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->product?->name} - {$record->full_name}")
                ->searchable()
                ->live()
                ->required(),
            Select::make('shared_account_id')
                ->label('Shared Account')
                ->helperText(function ($get) {
                    $plan = Plan::find($get('plan_id'));

                    return $plan?->hasFixedDeviceSlots()
                        ? "This plan needs {$plan->device_slots} free device slot(s) on the account — only accounts with enough room are listed."
                        : 'Pick which pooled account to assign this customer to. Only accounts with a free slot are listed.';
                })
                ->options(function ($get, ?Subscription $record) {
                    $plan = Plan::find($get('plan_id'));

                    if (! $plan || ! $plan->product?->is_shared_account) {
                        return [];
                    }

                    $required = $plan->device_slots ?? 1;

                    return SharedAccount::where('product_id', $plan->product_id)
                        ->get()
                        ->filter(fn (SharedAccount $account) => $account->availableSlots() + ($account->id === $record?->shared_account_id ? $record->slots_used : 0) >= $required)
                        ->mapWithKeys(fn (SharedAccount $account) => [
                            $account->id => "{$account->email} ({$account->usedSlots()}/{$account->max_slots} used)",
                        ]);
                })
                ->visible(fn ($get) => Plan::find($get('plan_id'))?->product?->is_shared_account)
                ->required(fn ($get) => Plan::find($get('plan_id'))?->product?->is_shared_account)
                ->live(),
            Select::make('slots_used')
                ->label('Devices')
                ->helperText('How many of this account\'s device slots does this customer occupy (1, 2, or 3)?')
                ->options(function ($get, ?Subscription $record) {
                    $account = SharedAccount::find($get('shared_account_id'));

                    if (! $account) {
                        return [];
                    }

                    $ownSlots = $account->id === $record?->shared_account_id ? $record->slots_used : 0;
                    $max = min($account->max_slots, $account->availableSlots() + $ownSlots);

                    return collect(range(1, max(1, $max)))->mapWithKeys(fn ($n) => [$n => "{$n} device".($n > 1 ? 's' : '')]);
                })
                ->default(1)
                ->visible(fn ($get) => Plan::find($get('plan_id'))?->product?->is_shared_account
                    && ! Plan::find($get('plan_id'))?->hasFixedDeviceSlots())
                ->required(fn ($get) => Plan::find($get('plan_id'))?->product?->is_shared_account
                    && ! Plan::find($get('plan_id'))?->hasFixedDeviceSlots()),
            TextInput::make('account_email')
                ->label('Account Email')
                ->helperText('Login email of the account given to this customer')
                ->visible(fn ($get) => ! Plan::find($get('plan_id'))?->product?->is_shared_account),
            TextInput::make('account_password')
                ->label('Account Password')
                ->password()
                ->revealable()
                ->visible(fn ($get) => ! Plan::find($get('plan_id'))?->product?->is_shared_account),
            Select::make('sale_source_id')
                ->label('Source of Sale')
                ->relationship('saleSource', 'name')
                ->searchable()
                ->preload(),
            Select::make('status')
                ->options([
                    Subscription::STATUS_PENDING => 'Pending',
                    Subscription::STATUS_ACTIVE => 'Active',
                    Subscription::STATUS_REJECTED => 'Rejected',
                    Subscription::STATUS_EXPIRED => 'Expired',
                    Subscription::STATUS_CANCELLED => 'Cancelled',
                ])
                ->required(),
            TextInput::make('total_days')
                ->label('Total Days Purchased')
                ->helperText('Expiry date is fixed from this at activation, regardless of recharge progress.')
                ->numeric()
                ->minValue(1),
            DateTimePicker::make('starts_at'),
            DateTimePicker::make('expires_at'),
            TextInput::make('amount')
                ->label('Customer Paid')
                ->numeric()
                ->prefix('NPR')
                ->required(),
            TextInput::make('credit_due')
                ->label('Credit (Amount Still Due)')
                ->numeric()
                ->prefix('NPR'),
            Textarea::make('admin_note')
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('Customer')->searchable()->sortable(),
                TextColumn::make('plan.product.name')->label('Product')->badge(),
                TextColumn::make('plan.full_name')->label('Plan'),
                BadgeColumn::make('status')
                    ->colors([
                        'warning' => Subscription::STATUS_PENDING,
                        'success' => Subscription::STATUS_ACTIVE,
                        'danger' => fn ($state) => in_array($state, [Subscription::STATUS_REJECTED, Subscription::STATUS_CANCELLED]),
                        'gray' => Subscription::STATUS_EXPIRED,
                    ]),
                TextColumn::make('total_days')->label('Total Days'),
                TextColumn::make('days_recharged')->label('Days Covered'),
                TextColumn::make('next_recharge_date')->label('Next Recharge')->date()->sortable(),
                TextColumn::make('expires_at')->dateTime()->sortable(),
                TextColumn::make('created_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options([
                    Subscription::STATUS_PENDING => 'Pending',
                    Subscription::STATUS_ACTIVE => 'Active',
                    Subscription::STATUS_REJECTED => 'Rejected',
                    Subscription::STATUS_EXPIRED => 'Expired',
                    Subscription::STATUS_CANCELLED => 'Cancelled',
                ]),
            ])
            ->actions([
                SubscriptionTableActions::approve(),
                SubscriptionTableActions::addRecharge(),
                SubscriptionTableActions::extend(),
                SubscriptionTableActions::cancel(),
                \Filament\Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptions::route('/'),
            'create' => Pages\CreateSubscription::route('/create'),
            'edit' => Pages\EditSubscription::route('/{record}/edit'),
        ];
    }
}
