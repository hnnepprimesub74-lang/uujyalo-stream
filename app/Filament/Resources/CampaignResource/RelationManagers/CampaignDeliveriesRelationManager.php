<?php

namespace App\Filament\Resources\CampaignResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CampaignDeliveriesRelationManager extends RelationManager
{
    protected static string $relationship = 'deliveries';

    protected static ?string $title = 'Deliveries';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->searchable()->sortable(),
                TextColumn::make('user.email')->label('Email')->searchable(),
                BadgeColumn::make('channel')->colors([
                    'info' => 'email',
                    'success' => 'sms',
                ]),
                BadgeColumn::make('status')->colors([
                    'success' => 'sent',
                    'danger' => 'failed',
                    'gray' => 'pending',
                ]),
                TextColumn::make('response')->limit(60),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('channel')->options(['email' => 'Email', 'sms' => 'SMS']),
                SelectFilter::make('status')->options(['sent' => 'Sent', 'failed' => 'Failed', 'pending' => 'Pending']),
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }

    public function canCreate(): bool
    {
        return false;
    }
}
