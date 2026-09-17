<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-tv';

    protected static ?string $navigationGroup = 'Catalog Setup';

    protected static ?int $navigationSort = 0;

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->required()
                ->live(onBlur: true)
                ->maxLength(255),
            TextInput::make('slug')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),
            FileUpload::make('image')
                ->label('Product Image')
                ->image()
                ->imageEditor()
                ->disk('public')
                ->directory('products')
                ->helperText('Shown on the storefront home page and product page. Falls back to a letter avatar when empty.'),
            Select::make('category')
                ->label('Category')
                ->options([
                    'Subscriptions' => 'Subscriptions',
                    'Game Service' => 'Game Service',
                    'AI Tools' => 'AI Tools',
                    'Design Tools' => 'Design Tools',
                    'Gift Cards' => 'Gift Cards',
                    'Other' => 'Other',
                ])
                ->native(false),
            Toggle::make('is_active')->default(true),
            Toggle::make('is_shared_account')
                ->label('Uses Shared Accounts')
                ->helperText('Turn on if one account is shared between multiple customers (e.g. CapCut, ChatGPT). A "[Product] Accounts" page will appear in the sidebar automatically to manage the pool.')
                ->live()
                ->default(false),
            TextInput::make('default_shared_slots')
                ->label('Default Slots Per Account')
                ->numeric()
                ->minValue(1)
                ->default(3)
                ->visible(fn ($get) => $get('is_shared_account')),
            ColorPicker::make('accent_color')
                ->label('Accent Color')
                ->helperText('Optional — themes this product\'s page (buttons, selections) to match its brand, e.g. Netflix red. Leave blank to use the site\'s default orange.'),

            Section::make('Trending Now')
                ->description('Shown at the top of the product page description, above "Description". Upload your own artwork for each title — do not use images you don\'t have the rights to.')
                ->collapsible()
                ->collapsed()
                ->columnSpanFull()
                ->schema([
                    Repeater::make('trending')
                        ->label('Titles')
                        ->schema([
                            FileUpload::make('image')
                                ->label('Poster')
                                ->image()
                                ->imageEditor()
                                ->disk('public')
                                ->directory('trending')
                                ->required(),
                            TextInput::make('title')
                                ->label('Title')
                                ->maxLength(255),
                        ])
                        ->columns(2)
                        ->reorderable()
                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                        ->collapsible()
                        ->columnSpanFull(),
                ]),

            Section::make('Product Page Content')
                ->description('Shown on the public product page, below the order form.')
                ->collapsible()
                ->columnSpanFull()
                ->schema([
                    Textarea::make('description')
                        ->label('Description')
                        ->helperText('The main marketing paragraph(s) shown under "Description".')
                        ->rows(4)
                        ->columnSpanFull(),
                    Textarea::make('highlight_note')
                        ->label('Highlight Note')
                        ->helperText('Optional short callout, e.g. "Plan features can change as OpenAI updates its models."')
                        ->rows(2)
                        ->columnSpanFull(),
                    Textarea::make('plan_guidance')
                        ->label('"Which Plan Should You Choose?" Guidance')
                        ->helperText('Optional paragraph(s) helping customers pick between plans.')
                        ->rows(3)
                        ->columnSpanFull(),
                    Repeater::make('faqs')
                        ->label('FAQs')
                        ->schema([
                            TextInput::make('question')->required()->columnSpanFull(),
                            Textarea::make('answer')->required()->rows(2)->columnSpanFull(),
                        ])
                        ->columns(1)
                        ->itemLabel(fn (array $state): ?string => $state['question'] ?? null)
                        ->collapsible()
                        ->columnSpanFull(),
                    TextInput::make('external_link_label')
                        ->label('External Link Label')
                        ->helperText('e.g. "Browse what\'s on Netflix" — leave blank to hide the button.')
                        ->maxLength(255),
                    TextInput::make('external_link_url')
                        ->label('External Link URL')
                        ->helperText('e.g. https://www.netflix.com/np/ — opens in a new tab.')
                        ->url()
                        ->maxLength(255),
                ]),

            Section::make('More Reasons To Join')
                ->description('Optional perk cards shown near the bottom of the product page, e.g. "Enjoy on your TV".')
                ->collapsible()
                ->collapsed()
                ->columnSpanFull()
                ->schema([
                    Repeater::make('perks')
                        ->label('Perks')
                        ->schema([
                            TextInput::make('title')->required()->columnSpanFull(),
                            Textarea::make('description')->rows(2)->columnSpanFull(),
                        ])
                        ->columns(1)
                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                        ->collapsible()
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')->disk('public')->label(''),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('category')->badge(),
                TextColumn::make('plans_count')->counts('plans')->label('Plans'),
                IconColumn::make('is_active')->boolean(),
                IconColumn::make('is_shared_account')->label('Shared')->boolean(),
                TextColumn::make('created_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                \Filament\Tables\Actions\EditAction::make(),
                \Filament\Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
