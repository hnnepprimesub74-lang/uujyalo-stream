<?php

namespace App\Filament\Pages;

use App\Filament\Support\SubscriptionTableActions;
use App\Models\Subscription;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Actions\Action as PageAction;

class PendingOrders extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $slug = 'pending-orders';

    protected static string $view = 'filament.pages.pending-orders';

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationLabel = 'Pending Orders';

    protected static ?string $navigationGroup = 'Subscriptions';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Pending Orders';

    public static function getNavigationBadge(): ?string
    {
        $count = static::baseQuery()->count();

        return $count > 0 ? (string) $count : null;
    }

    protected function getHeaderActions(): array
    {
        return [
            PageAction::make('addOrder')
                ->label('Add Order')
                ->icon('heroicon-o-plus-circle')
                ->url(fn () => AddOrder::getUrl()),
        ];
    }

    protected static function baseQuery()
    {
        return Subscription::query()
            ->where('status', Subscription::STATUS_ACTIVE)
            ->whereNull('starts_at')
            ->whereHas('plan.product', fn ($query) => $query->where('is_shared_account', false));
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(static::baseQuery())
            ->heading('Approved — Awaiting Order Creation')
            ->description('Payment has been verified for these private-account orders, but the account still needs to be created and confirmed. The customer\'s email/password from checkout is prefilled on "Create Order".')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('order_number')->label('Order ID')->copyable()->searchable(),
                TextColumn::make('user.name')->label('Customer')->searchable(),
                TextColumn::make('user.phone')->label('Phone')->searchable(),
                TextColumn::make('plan.product.name')->label('Product')->badge(),
                TextColumn::make('plan.full_name')->label('Plan'),
                TextColumn::make('amount')->label('Amount')->money('NPR'),
                TextColumn::make('account_email')->label('Account Email')->copyable()->placeholder('—'),
                TextColumn::make('account_password')->label('Password')->copyable()->placeholder('—'),
                TextColumn::make('created_at')->label('Approved')->dateTime()->sortable(),
            ])
            ->actions([
                SubscriptionTableActions::details()->iconButton(),
                Action::make('createOrder')
                    ->label('Create Order')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->url(fn (Subscription $record) => AddOrder::getUrl(['subscriptionId' => $record->id])),
            ])
            ->emptyStateHeading('Nothing waiting')
            ->emptyStateDescription('Every approved private-account order has already been created.');
    }
}
