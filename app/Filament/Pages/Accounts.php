<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Models\SharedAccount;
use Filament\Pages\Page;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Attributes\Url;

class Accounts extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $slug = 'accounts';

    protected static string $view = 'filament.pages.accounts';

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Accounts';

    protected static ?int $navigationSort = 21;

    protected static ?string $title = 'Accounts';

    protected ?string $maxContentWidth = 'full';

    #[Url]
    public ?string $productSlug = null;

    public static function shouldRegisterNavigation(): bool
    {
        return static::getProducts()->isNotEmpty();
    }

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
            ->where('is_shared_account', true)
            ->orderBy('name')
            ->get();
    }

    public function getActiveProduct(): ?Product
    {
        return static::getProducts()->firstWhere('slug', $this->productSlug);
    }

    public function getBulkAddUrl(): ?string
    {
        $product = $this->getActiveProduct();

        return $product ? BulkAddProductAccounts::getUrl(['product' => $product]) : null;
    }

    public function table(Table $table): Table
    {
        $productId = $this->getActiveProduct()?->id;

        return $table
            ->query(SharedAccount::query()->where('product_id', $productId))
            ->columns([
                TextColumn::make('email')->label('Account Email')->copyable()->searchable(false),
                TextColumn::make('password')->label('Password')->copyable()->searchable(false),
                TextColumn::make('slots')
                    ->label('Slots Used')
                    ->getStateUsing(fn (SharedAccount $record) => $record->usedSlots().' / '.$record->max_slots)
                    ->color(fn (SharedAccount $record) => $record->hasAvailableSlot() ? 'success' : 'danger'),
                TextColumn::make('account_expires_at')
                    ->label('Account Expires')
                    ->getStateUsing(fn (SharedAccount $record) => $record->accountExpiresAt()?->format('M j, Y') ?? '—')
                    ->color(fn (SharedAccount $record) => $record->needsRotation() ? 'danger' : null)
                    ->weight(fn (SharedAccount $record) => $record->needsRotation() ? 'bold' : null)
                    ->tooltip(fn (SharedAccount $record) => $record->needsRotation()
                        ? 'This account is expiring soon and still has active customers — rotate in new credentials.'
                        : null),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('notes')->limit(40)->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort(fn ($query) => $query->orderByRaw(
                'CASE WHEN purchased_at IS NOT NULL AND duration_days IS NOT NULL THEN DATE_ADD(purchased_at, INTERVAL duration_days DAY) ELSE NULL END IS NULL, '.
                'DATE_ADD(purchased_at, INTERVAL duration_days DAY) ASC'
            ))
            ->actions([
                EditAction::make()
                    ->url(fn (SharedAccount $record) => route('filament.admin.resources.shared-accounts.edit', $record)),
                DeleteAction::make(),
            ]);
    }
}
