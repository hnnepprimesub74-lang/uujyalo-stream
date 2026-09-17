<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleSourceResource\Pages;
use App\Models\SaleSource;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SaleSourceResource extends Resource
{
    protected static ?string $model = SaleSource::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationLabel = 'Sales Sources';

    protected static ?string $modelLabel = 'sales source';

    protected static ?string $pluralModelLabel = 'sales sources';

    protected static ?string $navigationGroup = 'Catalog Setup';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->label('Source Name')
                ->helperText('e.g. WhatsApp 1, WhatsApp 2, Instagram, Facebook')
                ->required()
                ->maxLength(255),
            Textarea::make('details')
                ->label('Details')
                ->helperText('e.g. the number, page name, or notes about this channel')
                ->columnSpanFull(),
            Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('details')->limit(50),
                TextColumn::make('subscriptions_count')->counts('subscriptions')->label('Orders'),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('created_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->actions([
                \Filament\Tables\Actions\EditAction::make(),
                \Filament\Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSaleSources::route('/'),
            'create' => Pages\CreateSaleSource::route('/create'),
            'edit' => Pages\EditSaleSource::route('/{record}/edit'),
        ];
    }
}
