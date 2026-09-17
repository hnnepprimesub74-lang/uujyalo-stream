<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationLogResource\Pages;
use App\Models\NotificationLog;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class NotificationLogResource extends Resource
{
    protected static ?string $model = NotificationLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';

    protected static ?string $navigationGroup = 'Subscriptions';

    protected static ?int $navigationSort = 13;

    protected static bool $canCreate = false;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->searchable()->sortable(),
                BadgeColumn::make('channel')->colors([
                    'info' => 'email',
                    'success' => 'sms',
                ]),
                TextColumn::make('type'),
                BadgeColumn::make('status')->colors([
                    'success' => 'sent',
                    'danger' => 'failed',
                ]),
                TextColumn::make('message')->limit(50),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('channel')->options(['email' => 'Email', 'sms' => 'SMS']),
                SelectFilter::make('status')->options(['sent' => 'Sent', 'failed' => 'Failed']),
            ])
            ->actions([]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotificationLogs::route('/'),
        ];
    }
}
