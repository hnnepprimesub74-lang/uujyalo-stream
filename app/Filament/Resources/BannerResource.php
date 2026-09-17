<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BannerResource\Pages;
use App\Models\Banner;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Offer Banners';

    protected static ?string $modelLabel = 'banner';

    protected static ?string $navigationGroup = 'Catalog Setup';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            FileUpload::make('image')
                ->label('Banner Image')
                ->image()
                ->imageEditor()
                ->required()
                ->disk('public')
                ->directory('banners')
                ->helperText('Shown as a full-width slide on the storefront home page. Recommended: wide image, e.g. 1200x500.')
                ->columnSpanFull(),
            TextInput::make('title')
                ->label('Title')
                ->helperText('Optional — used as alt text and internal reference.')
                ->maxLength(255),
            TextInput::make('link_url')
                ->label('Link URL')
                ->helperText('Where the slide goes when tapped. Leave blank for no link.')
                ->url(),
            TextInput::make('sort_order')
                ->label('Sort Order')
                ->numeric()
                ->default(0)
                ->helperText('Lower numbers show first.'),
            Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')->disk('public')->label(''),
                TextColumn::make('title')->searchable(),
                TextColumn::make('link_url')->limit(40)->label('Link'),
                TextColumn::make('sort_order')->sortable(),
                IconColumn::make('is_active')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->actions([
                \Filament\Tables\Actions\EditAction::make(),
                \Filament\Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'edit' => Pages\EditBanner::route('/{record}/edit'),
        ];
    }
}
