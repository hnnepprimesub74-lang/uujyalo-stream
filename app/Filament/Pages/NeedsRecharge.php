<?php

namespace App\Filament\Pages;

use App\Filament\Support\SubscriptionTableActions;
use App\Models\Subscription;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

class NeedsRecharge extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?string $navigationLabel = 'Needs Recharge';

    protected static ?string $navigationGroup = 'Subscriptions';

    protected static ?int $navigationSort = 9;

    protected static string $view = 'filament.pages.needs-recharge';

    public const LOOKAHEAD_DAYS = 7;

    protected function baseQuery()
    {
        return Subscription::query()
            ->where('status', Subscription::STATUS_ACTIVE)
            ->whereNotNull('total_days')
            ->whereColumn('days_recharged', '<', 'total_days')
            ->whereNotNull('next_recharge_date');
    }

    public function getOverdueCount(): int
    {
        return $this->baseQuery()
            ->where('next_recharge_date', '<', Carbon::today())
            ->count();
    }

    public function getDueSoonCount(): int
    {
        return $this->baseQuery()
            ->whereBetween('next_recharge_date', [Carbon::today(), Carbon::today()->addDays(self::LOOKAHEAD_DAYS)])
            ->count();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                $this->baseQuery()->where('next_recharge_date', '<=', Carbon::today()->addDays(self::LOOKAHEAD_DAYS))
            )
            ->columns([
                TextColumn::make('order_number')->label('Order ID')->copyable()->searchable(),
                TextColumn::make('user.name')->label('Customer')->searchable(),
                TextColumn::make('user.phone')->label('Phone'),
                TextColumn::make('plan.product.name')->label('Product')->badge(),
                TextColumn::make('plan.full_name')->label('Plan'),
                TextColumn::make('account_email')->label('Account Email'),
                TextColumn::make('total_days')->label('Total Days'),
                TextColumn::make('days_covered')
                    ->label('Days Covered')
                    ->getStateUsing(fn (Subscription $record) => $record->remainingRechargeDays() ?? '—')
                    ->color(fn (Subscription $record) => $record->remainingRechargeDays() < 0 ? 'danger' : 'warning')
                    ->weight('bold'),
                TextColumn::make('next_recharge_date')
                    ->label('Recharge Due')
                    ->date()
                    ->sortable()
                    ->color(fn (Subscription $record) => $record->next_recharge_date->isPast() ? 'danger' : 'warning'),
                TextColumn::make('expires_at')->label('Final Expiry')->date(),
            ])
            ->defaultSort('next_recharge_date')
            ->actions([
                SubscriptionTableActions::details()->iconButton(),
                SubscriptionTableActions::addRecharge(),
            ]);
    }
}
