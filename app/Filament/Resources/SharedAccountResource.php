<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SharedAccountResource\Pages;
use App\Models\SharedAccount;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SharedAccountResource extends Resource
{
    protected static ?string $model = SharedAccount::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Shared Accounts';

    protected static ?string $modelLabel = 'shared account';

    protected static ?string $pluralModelLabel = 'shared accounts';

    protected static ?string $navigationGroup = 'Subscriptions';

    /**
     * Superseded by the dynamic per-product ProductAccounts page. This
     * resource stays registered only to serve its edit route.
     */
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('product_id')
                ->label('Product')
                ->relationship('product', 'name', fn ($query) => $query->where('is_shared_account', true))
                ->searchable()
                ->required(),
            TextInput::make('email')
                ->label('Account Email')
                ->required(),
            TextInput::make('password')
                ->label('Account Password')
                ->password()
                ->revealable()
                ->required(),
            TextInput::make('max_slots')
                ->label('Max Slots (how many customers can share this account)')
                ->numeric()
                ->minValue(1)
                ->default(3)
                ->required(),
            DatePicker::make('purchased_at')
                ->label('Purchased / Activated On')
                ->helperText('When you bought or last renewed this account.')
                ->default(now()),
            TextInput::make('duration_days')
                ->label('Account Valid For (days)')
                ->helperText('How long this account itself lasts before you need to rotate in new credentials. Leave blank if it doesn\'t expire.')
                ->numeric()
                ->minValue(1),
            Toggle::make('is_active')->default(true),
            Textarea::make('notes')
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')->label('Product')->badge()->sortable(),
                TextColumn::make('email')->label('Account Email')->copyable()->searchable(false),
                TextColumn::make('password')->label('Account Password')->copyable()->searchable(false),
                TextColumn::make('slots')
                    ->label('Slots Used')
                    ->getStateUsing(fn (SharedAccount $record) => $record->usedSlots().' / '.$record->max_slots)
                    ->color(fn (SharedAccount $record) => $record->hasAvailableSlot() ? 'success' : 'danger'),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('notes')->limit(40)->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('product_id')
                    ->label('Product')
                    ->relationship('product', 'name', fn ($query) => $query->where('is_shared_account', true)),
            ])
            ->actions([
                \Filament\Tables\Actions\EditAction::make(),
                \Filament\Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSharedAccounts::route('/'),
            'edit' => Pages\EditSharedAccount::route('/{record}/edit'),
        ];
    }
}
