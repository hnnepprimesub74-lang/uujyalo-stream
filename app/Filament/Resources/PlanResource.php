<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanResource\Pages;
use App\Models\Plan;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Catalog Setup';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('product_id')
                ->label('Product')
                ->relationship('product', 'name')
                ->required()
                ->searchable()
                ->preload()
                ->live(),
            TextInput::make('type')
                ->label('Type')
                ->helperText('The tier, e.g. Mobile, Basic, Standard, Premium')
                ->maxLength(255),
            TextInput::make('badge_label')
                ->label('Badge Label')
                ->helperText('Optional pill shown on this plan\'s card, e.g. "ADVANCED" or "POPULAR"')
                ->maxLength(50),
            TextInput::make('name')
                ->label('Name')
                ->helperText('The duration label shown to customers, e.g. 150 Days')
                ->required()
                ->live(onBlur: true)
                ->maxLength(255),
            TextInput::make('slug')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),
            Textarea::make('description')
                ->columnSpanFull(),
            TextInput::make('duration_days')
                ->label('Default Duration (days)')
                ->required()
                ->numeric()
                ->minValue(1),
            TextInput::make('device_slots')
                ->label('Device Slots (min)')
                ->helperText('Leave blank to let the admin pick devices per order. Set a number to fix it (e.g. 3 for a private/exclusive account), or the low end of a range (e.g. 3 of "3–4 devices").')
                ->numeric()
                ->minValue(1),
            TextInput::make('device_slots_max')
                ->label('Device Slots (max)')
                ->helperText('Optional — only set this if the device count is a range (e.g. 4 for "3–4 devices"). Leave blank for a fixed number.')
                ->numeric()
                ->minValue(1),
            TextInput::make('device_label')
                ->label('Device Limit Wording')
                ->helperText('What the number above means, e.g. "Device Login" (CapCut/ChatGPT — how many devices can be logged in) or "Screen at a Time" (Netflix — unlimited logins, limited concurrent streams). Defaults to "Device Login" if left blank.')
                ->maxLength(50),
            TextInput::make('quality')
                ->label('Screen Quality')
                ->helperText('Optional, e.g. HD, Full HD, 4K — shown on the storefront to help customers compare tiers.')
                ->maxLength(50),
            TextInput::make('supported_devices')
                ->label('Supported Devices')
                ->helperText('Optional, e.g. "Phone, Tablet" or "TV, Mobile, Laptop" — shown on the storefront.')
                ->maxLength(100),
            TextInput::make('price')
                ->label('Customer Price')
                ->required()
                ->numeric()
                ->prefix('NPR'),
            TextInput::make('monthly_cost')
                ->label('Our Cost (per month)')
                ->helperText(fn ($get) => \App\Models\Product::find($get('product_id'))?->is_shared_account
                    ? 'Optional for shared-account products — the account\'s own cost is tracked on the CapCut Accounts page instead.'
                    : 'What this account tier costs us per month, e.g. 3.99')
                ->required(fn ($get) => ! \App\Models\Product::find($get('product_id'))?->is_shared_account)
                ->numeric()
                ->prefix('$'),
            Repeater::make('features')
                ->simple(
                    TextInput::make('feature')->required()
                )
                ->columnSpanFull(),
            TextInput::make('sort_order')
                ->numeric()
                ->default(0),
            Toggle::make('is_active')
                ->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')->label('Product')->badge()->sortable(),
                TextColumn::make('type')->label('Type')->badge()->color('gray')->sortable()->searchable(),
                TextColumn::make('name')->label('Name')->searchable()->sortable(),
                TextColumn::make('duration_days')->label('Days')->sortable(),
                TextColumn::make('device_slots')->label('Devices')->placeholder('Flexible')->toggleable()
                    ->formatStateUsing(fn ($state, $record) => $record->device_slots_max && $record->device_slots_max !== $state
                        ? "{$state}–{$record->device_slots_max}"
                        : $state),
                TextColumn::make('quality')->label('Quality')->placeholder('—')->toggleable(),
                TextColumn::make('supported_devices')->label('Devices')->placeholder('—')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('price')->money('NPR')->sortable(),
                TextColumn::make('monthly_cost')->money('USD')->label('Our Cost/mo'),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('subscriptions_count')->counts('subscriptions')->label('Subscribers'),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->modifyQueryUsing(fn ($query) => $query
                ->orderBy(\App\Models\Product::select('name')->whereColumn('products.id', 'plans.product_id'))
                ->orderBy('type')
                ->orderBy('duration_days'))
            ->defaultGroup('product.name')
            ->groups([
                Group::make('product.name')
                    ->label('Product')
                    ->collapsible(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('product_id')
                    ->label('Product')
                    ->relationship('product', 'name'),
            ])
            ->actions([
                \Filament\Tables\Actions\EditAction::make(),
                \Filament\Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlans::route('/'),
            'edit' => Pages\EditPlan::route('/{record}/edit'),
        ];
    }
}
