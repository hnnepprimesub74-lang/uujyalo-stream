<?php

namespace App\Filament\Pages;

use App\Filament\Support\SubscriptionTableActions;
use App\Models\Subscription;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

class ExpiredSubscriptions extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-face-frown';

    protected static ?string $navigationLabel = 'Expired Subscriptions';

    protected static ?string $navigationGroup = 'Subscriptions';

    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.pages.expired-subscriptions';

    public function table(Table $table): Table
    {
        $graceCutoff = Carbon::today()->subDays(SubscriptionTableActions::RECHARGE_GRACE_DAYS);

        return $table
            ->query(
                Subscription::query()
                    ->where(function ($query) use ($graceCutoff) {
                        $query->where('status', Subscription::STATUS_EXPIRED)
                            ->orWhere(function ($q) use ($graceCutoff) {
                                $q->where('status', Subscription::STATUS_ACTIVE)
                                    ->whereColumn('days_recharged', '<', 'total_days')
                                    ->whereNotNull('next_recharge_date')
                                    ->where('next_recharge_date', '<', $graceCutoff->toDateString());
                            });
                    })
                    ->orderBy('next_recharge_date', 'desc')
            )
            ->columns([
                TextColumn::make('user.name')->label('Customer')->searchable()->sortable(),
                TextColumn::make('user.phone')->label('Phone')->searchable(),
                TextColumn::make('user.email')->label('Email')->searchable(),
                TextColumn::make('plan.product.name')->label('Product')->badge(),
                TextColumn::make('plan.full_name')->label('Plan'),
                TextColumn::make('days_recharged')->label('Days Covered'),
                TextColumn::make('total_days')->label('Total Days'),
                TextColumn::make('next_recharge_date')->label('Recharge Was Due')->date()->color('danger')->sortable(),
            ])
            ->filters([
                SelectFilter::make('product')
                    ->relationship('plan.product', 'name'),
            ])
            ->actions([
                SubscriptionTableActions::details()->iconButton(),
                SubscriptionTableActions::reactivateAndRecharge(),
                SubscriptionTableActions::cancel(),
            ]);
    }
}
