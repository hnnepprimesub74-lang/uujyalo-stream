<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationLabel = 'Coupons';

    protected static ?string $navigationGroup = 'Subscriptions';

    protected static ?int $navigationSort = 16;

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('code')
                ->label('Coupon Code')
                ->helperText('Customers type this at checkout, e.g. SAVE50. Not case-sensitive.')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(50),
            Select::make('type')
                ->label('Discount Type')
                ->options([
                    Coupon::TYPE_FIXED => 'Fixed Amount (NPR)',
                    Coupon::TYPE_PERCENTAGE => 'Percentage (%)',
                ])
                ->default(Coupon::TYPE_FIXED)
                ->required()
                ->live()
                ->native(false),
            TextInput::make('value')
                ->label(fn ($get) => $get('type') === Coupon::TYPE_PERCENTAGE ? 'Discount Percentage' : 'Discount Amount (NPR)')
                ->required()
                ->numeric()
                ->minValue(0)
                ->maxValue(fn ($get) => $get('type') === Coupon::TYPE_PERCENTAGE ? 100 : null),
            TextInput::make('min_order_amount')
                ->label('Minimum Order Amount (NPR)')
                ->helperText('Optional — leave blank for no minimum.')
                ->numeric()
                ->minValue(0),
            DatePicker::make('starts_at')
                ->label('Start Date')
                ->helperText('Optional — leave blank to start immediately.'),
            DatePicker::make('ends_at')
                ->label('End Date')
                ->helperText('Optional — leave blank for no expiry.')
                ->afterOrEqual('starts_at'),
            Toggle::make('is_active')
                ->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->badge()->searchable()->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (string $state) => $state === Coupon::TYPE_PERCENTAGE ? 'Percentage' : 'Fixed'),
                TextColumn::make('value')
                    ->label('Discount')
                    ->formatStateUsing(fn ($record) => $record->type === Coupon::TYPE_PERCENTAGE
                        ? number_format((float) $record->value, 0).'%'
                        : 'NPR '.number_format((float) $record->value, 0)),
                TextColumn::make('min_order_amount')
                    ->label('Min. Order')
                    ->formatStateUsing(fn ($state) => $state ? 'NPR '.number_format((float) $state, 0) : '—'),
                TextColumn::make('starts_at')->label('Starts')->date()->placeholder('Anytime'),
                TextColumn::make('ends_at')->label('Ends')->date()->placeholder('No expiry'),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('created_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                \Filament\Tables\Actions\EditAction::make(),
                \Filament\Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
