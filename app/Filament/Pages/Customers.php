<?php

namespace App\Filament\Pages;

use App\Filament\Support\SubscriptionTableActions;
use App\Models\Product;
use App\Models\Subscription;
use Filament\Pages\Page;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Url;

class Customers extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $slug = 'customers';

    protected static string $view = 'filament.pages.customers';

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Customer';

    protected static ?int $navigationSort = 20;

    protected static ?string $title = 'Customers';

    protected ?string $maxContentWidth = 'full';

    #[Url]
    public ?string $productSlug = null;

    public function mount(): void
    {
        $this->productSlug ??= static::getProducts()->first()?->slug;
    }

    public function selectProduct(string $slug): void
    {
        $this->productSlug = $slug;
        $this->resetTable();
    }

    public static function getProducts()
    {
        return Product::query()
            ->where('is_active', true)
            ->whereHas('plans', fn ($query) => $query->where('is_active', true))
            ->orderBy('name')
            ->get();
    }

    public function getActiveProduct(): ?Product
    {
        return static::getProducts()->firstWhere('slug', $this->productSlug);
    }

    public function table(Table $table): Table
    {
        $graceCutoff = Carbon::today()->subDays(SubscriptionTableActions::RECHARGE_GRACE_DAYS);
        $productId = $this->getActiveProduct()?->id;

        return $table
            ->query(
                Subscription::query()
                    ->whereHas('plan', fn ($query) => $query->where('product_id', $productId))
                    ->where('status', Subscription::STATUS_ACTIVE)
                    ->whereNotNull('starts_at')
                    ->where(function ($query) use ($graceCutoff) {
                        $query->whereNull('next_recharge_date')
                            ->orWhere('next_recharge_date', '>=', $graceCutoff->toDateString());
                    })
                    ->orderByRaw('CASE WHEN next_recharge_date IS NOT NULL AND next_recharge_date < CURDATE() THEN 1 ELSE 0 END ASC')
                    ->orderBy('next_recharge_date', 'asc')
            )
            ->columns([
                TextColumn::make('user.name')->label('Customer')->searchable()->sortable(),
                TextColumn::make('user.phone')->label('Phone')->searchable(),
                TextColumn::make('account_email')
                    ->label('Account Email')
                    ->getStateUsing(fn (Subscription $record) => $record->displayAccountEmail())
                    ->copyable()
                    ->searchable(false),
                TextColumn::make('account_password')
                    ->label('Account Password')
                    ->getStateUsing(fn (Subscription $record) => $record->displayAccountPassword())
                    ->copyable()
                    ->searchable(false),
                TextColumn::make('total_days')->label('Total Days')->alignCenter(),
                TextColumn::make('days_covered')
                    ->label('Days Covered')
                    ->alignCenter()
                    ->getStateUsing(fn (Subscription $record) => $record->remainingRechargeDays() ?? '—')
                    ->color(fn (Subscription $record) => match (true) {
                        $record->remainingRechargeDays() === null => null,
                        $record->remainingRechargeDays() < 0 => 'danger',
                        $record->remainingRechargeDays() <= 7 => 'warning',
                        default => null,
                    })
                    ->weight(fn (Subscription $record) => $record->remainingRechargeDays() !== null && $record->remainingRechargeDays() <= 7 ? 'bold' : null)
                    ->tooltip('Days left until the next recharge is due (counts down daily)'),
                TextColumn::make('next_recharge_date')
                    ->label('Next Recharge')
                    ->date('M j, Y')
                    ->sortable()
                    ->color(fn ($record) => $record->next_recharge_date && $record->next_recharge_date->isPast() ? 'danger' : null)
                    ->weight(fn ($record) => $record->next_recharge_date && $record->next_recharge_date->isPast() ? 'bold' : null),
                TextColumn::make('remaining_days')
                    ->label('Remaining Days')
                    ->alignCenter()
                    ->getStateUsing(fn (Subscription $record) => $record->daysUntilExpiry()),
                TextColumn::make('expires_at')->label('Expires At')->date('M j, Y')->sortable(),
                TextColumn::make('saleSource.name')->label('Source')->badge()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('plan.full_name')->label('Plan')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('credit_due')
                    ->label('Due')
                    ->money('NPR')
                    ->color(fn ($record) => $record->credit_due > 0 ? 'danger' : null)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('plan_id')
                    ->label('Plan')
                    ->relationship('plan', 'name', fn ($query) => $query->where('product_id', $productId)),
                SelectFilter::make('sale_source_id')
                    ->label('Source')
                    ->relationship('saleSource', 'name'),
            ])
            ->actions([
                SubscriptionTableActions::details()->iconButton(),
                SubscriptionTableActions::approve()->iconButton(),
                SubscriptionTableActions::addRecharge()->iconButton(),
                EditAction::make()
                    ->iconButton()
                    ->url(fn (Subscription $record) => route('filament.admin.resources.subscriptions.edit', $record)),
            ])
            ->extremePaginationLinks()
            ->paginated([10, 25, 50, 100]);
    }
}
