<?php

namespace App\Filament\Resources;

use App\Filament\Imports\MarketingContactImporter;
use App\Filament\Resources\MarketingContactResource\Pages;
use App\Models\MarketingContact;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MarketingContactResource extends Resource
{
    protected static ?string $model = MarketingContact::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationLabel = 'Contacts';

    protected static ?string $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 21;

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')->maxLength(255),
            TextInput::make('email')->email()->maxLength(255),
            TextInput::make('phone')->tel()->maxLength(20),
            Toggle::make('marketing_opt_out')->label('Opted out of marketing'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->placeholder('—'),
                TextColumn::make('email')->searchable()->placeholder('—'),
                TextColumn::make('phone')->searchable()->placeholder('—'),
                IconColumn::make('receiving_messages')
                    ->label('Receiving Messages')
                    ->state(fn (MarketingContact $record) => ! $record->marketing_opt_out)
                    ->boolean(),
                TextColumn::make('created_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                ImportAction::make()
                    ->importer(MarketingContactImporter::class)
                    ->label('Import Contacts'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMarketingContacts::route('/'),
            'create' => Pages\CreateMarketingContact::route('/create'),
            'edit' => Pages\EditMarketingContact::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }
}
