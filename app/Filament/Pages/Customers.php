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

    public const ALL_SLUG = 'all';

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

    public function isAllSelected(): bool
    {
        return $this->productSlug === self::ALL_SLUG;
    }

    public function getActiveProduct(): ?Product
    {
        return static::getProducts()->firstWhere('slug', $this->productSlug);
    }

    public function getAllOrdersCount(): int
    {
        return Subscription::where('status', Subscription::STATUS_ACTIVE)
            ->whereNotNull('starts_at')
            ->count();
    }

    public function getProductCustomerCount(Product $product): int
    {
        return Subscription::where('status', Subscription::STATUS_ACTIVE)
            ->whereHas('plan', fn ($query) => $query->where('product_id', $product->id))
            ->count();
    }

    public function table(Table $table): Table
    {
        if ($this->isAllSelected()) {
            return $this->allOrdersTable($table);
        }

        $graceCutoff = Carbon::today()->subDays(SubscriptionTableActions::RECHARGE_GRACE_DAYS);
        $activeProduct = $this->getActiveProduct();
        $productId = $activeProduct?->id;
        $isSharedAccount = (bool) $activeProduct?->is_shared_account;

        $daysCoveredColumn = $isSharedAccount
            ? TextColumn::make('days_covered')
                ->label('Days Covered')
                ->alignCenter()
                ->getStateUsing(fn (Subscription $record) => $record->sharedAccount?->daysUntilAccountExpiry() ?? '—')
                ->color(fn (Subscription $record) => match (true) {
                    $record->sharedAccount?->daysUntilAccountExpiry() === null => null,
                    $record->sharedAccount->daysUntilAccountExpiry() <= 0 => 'danger',
                    $record->sharedAccount->daysUntilAccountExpiry() <= 3 => 'warning',
                    default => null,
                })
                ->weight(fn (Subscription $record) => $record->sharedAccount?->daysUntilAccountExpiry() !== null && $record->sharedAccount->daysUntilAccountExpiry() <= 3 ? 'bold' : null)
                ->tooltip('Days left until this customer\'s assigned account itself expires')
            : TextColumn::make('days_covered')
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
                ->tooltip('Days left until the next recharge is due (counts down daily)');

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
            ->columns(array_filter([
                TextColumn::make('order_number')->label('Order ID')->copyable()->searchable(),
                TextColumn::make('user.name')->label('Customer')->searchable()->sortable(),
                TextColumn::make('user.phone')->label('Phone')->searchable(),
                TextColumn::make('account_email')
                    ->label('Account Email')
                    ->getStateUsing(fn (Subscription $record) => $record->displayAccountEmail())
                    ->copyable()
                    ->searchable(false),
                TextColumn::make('account_password')
                    ->label('Password')
                    ->getStateUsing(fn (Subscription $record) => $record->displayAccountPassword())
                    ->copyable()
                    ->searchable(false),
                TextColumn::make('total_days')->label('Total Days')->alignCenter(),
                $daysCoveredColumn,
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
            ]))
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

    protected function allOrdersTable(Table $table): Table
    {
        $graceCutoff = Carbon::today()->subDays(SubscriptionTableActions::RECHARGE_GRACE_DAYS);

        return $table
            ->query(
                Subscription::query()
                    ->where('status', Subscription::STATUS_ACTIVE)
                    ->whereNotNull('starts_at')
                    ->where(function ($query) use ($graceCutoff) {
                        $query->whereNull('next_recharge_date')
                            ->orWhere('next_recharge_date', '>=', $graceCutoff->toDateString());
                    })
            )
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('order_number')->label('Order ID')->copyable()->searchable(),
                TextColumn::make('user.name')->label('Customer')->searchable()->sortable(),
                TextColumn::make('user.phone')->label('Phone')->searchable(),
                TextColumn::make('plan.product.name')->label('Product')->badge(),
                TextColumn::make('account_email')
                    ->label('Account Email')
                    ->getStateUsing(fn (Subscription $record) => $record->displayAccountEmail())
                    ->copyable()
                    ->searchable(false),
                TextColumn::make('account_password')
                    ->label('Password')
                    ->getStateUsing(fn (Subscription $record) => $record->displayAccountPassword())
                    ->copyable()
                    ->searchable(false),
                TextColumn::make('total_days')
                    ->label('Total Days')
                    ->alignCenter()
                    ->wrapHeader()
                    ->extraHeaderAttributes(['style' => 'max-width: 70px']),
                TextColumn::make('days_covered')
                    ->label('Days Covered')
                    ->alignCenter()
                    ->wrapHeader()
                    ->extraHeaderAttributes(['style' => 'max-width: 80px'])
                    ->getStateUsing(function (Subscription $record) {
                        if ($record->plan?->product?->is_shared_account) {
                            return $record->sharedAccount?->daysUntilAccountExpiry() ?? '—';
                        }

                        return $record->remainingRechargeDays() ?? '—';
                    })
                    ->color(function (Subscription $record) {
                        $days = $record->plan?->product?->is_shared_account
                            ? $record->sharedAccount?->daysUntilAccountExpiry()
                            : $record->remainingRechargeDays();

                        return match (true) {
                            $days === null => null,
                            $days <= 0 => 'danger',
                            $days <= 7 => 'warning',
                            default => null,
                        };
                    }),
                TextColumn::make('remaining_days')
                    ->label('Remaining Days')
                    ->alignCenter()
                    ->wrapHeader()
                    ->extraHeaderAttributes(['style' => 'max-width: 90px'])
                    ->getStateUsing(fn (Subscription $record) => $record->daysUntilExpiry()),
                TextColumn::make('expires_at')->label('Expires At')->date('M j, Y')->sortable(),
            ])
            ->filters([
                SelectFilter::make('product')
                    ->label('Product')
                    ->options(fn () => Product::pluck('name', 'id'))
                    ->query(fn ($query, $data) => $query->when(
                        $data['value'] ?? null,
                        fn ($query, $productId) => $query->whereHas('plan', fn ($q) => $q->where('product_id', $productId))
                    )),
                SelectFilter::make('sale_source_id')
                    ->label('Source')
                    ->relationship('saleSource', 'name'),
            ])
            ->actions([
                SubscriptionTableActions::details()->iconButton(),
                SubscriptionTableActions::addRecharge()->iconButton(),
                EditAction::make()
                    ->iconButton()
                    ->url(fn (Subscription $record) => route('filament.admin.resources.subscriptions.edit', $record)),
            ])
            ->extremePaginationLinks()
            ->paginated([10, 25, 50, 100]);
    }
}
