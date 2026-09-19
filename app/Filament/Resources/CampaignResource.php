<?php

namespace App\Filament\Resources;

use App\Actions\DispatchCampaignAction;
use App\Filament\Resources\CampaignResource\Pages;
use App\Filament\Resources\CampaignResource\RelationManagers\CampaignDeliveriesRelationManager;
use App\Models\Campaign;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CampaignResource extends Resource
{
    protected static ?string $model = Campaign::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 20;

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('subject')
                ->label('Email Subject')
                ->required(fn ($get) => $get('send_email'))
                ->maxLength(255)
                ->columnSpanFull(),
            Toggle::make('send_email')
                ->label('Send Email')
                ->default(true)
                ->live(),
            Textarea::make('email_body')
                ->label('Email Body')
                ->helperText('Supports basic markdown (e.g. **bold**, [link](https://...)).')
                ->rows(6)
                ->visible(fn ($get) => $get('send_email'))
                ->required(fn ($get) => $get('send_email'))
                ->columnSpanFull(),
            Toggle::make('send_sms')
                ->label('Send SMS')
                ->live(),
            Textarea::make('sms_body')
                ->label('SMS Text')
                ->rows(3)
                ->live()
                ->visible(fn ($get) => $get('send_sms'))
                ->required(fn ($get) => $get('send_sms'))
                ->helperText(function ($get) {
                    $length = strlen((string) $get('sms_body'));
                    $segments = max(1, (int) ceil($length / 160));

                    return "{$length} characters — {$segments} SMS segment(s).";
                })
                ->columnSpanFull(),
            Select::make('audience_filter')
                ->label('Audience')
                ->options([
                    Campaign::AUDIENCE_ALL => 'All customers',
                    Campaign::AUDIENCE_ACTIVE_SUBSCRIBERS => 'Customers with an active subscription',
                    Campaign::AUDIENCE_BY_PLAN => 'Customers on a specific plan',
                ])
                ->default(Campaign::AUDIENCE_ALL)
                ->required()
                ->live()
                ->native(false),
            Select::make('audience_plan_id')
                ->label('Plan')
                ->relationship('plan', 'name')
                ->visible(fn ($get) => $get('audience_filter') === Campaign::AUDIENCE_BY_PLAN)
                ->required(fn ($get) => $get('audience_filter') === Campaign::AUDIENCE_BY_PLAN)
                ->searchable()
                ->preload(),
            DateTimePicker::make('scheduled_at')
                ->label('Schedule For')
                ->helperText('Leave blank to send immediately via the Send Now action.')
                ->native(false)
                ->minDate(now())
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subject')->searchable()->limit(40),
                TextColumn::make('channels')
                    ->label('Channels')
                    ->state(function (Campaign $record) {
                        $channels = [];
                        if ($record->send_email) {
                            $channels[] = 'Email';
                        }
                        if ($record->send_sms) {
                            $channels[] = 'SMS';
                        }

                        return implode(' + ', $channels) ?: '—';
                    }),
                BadgeColumn::make('status')->colors([
                    'gray' => Campaign::STATUS_DRAFT,
                    'warning' => Campaign::STATUS_SCHEDULED,
                    'info' => Campaign::STATUS_SENDING,
                    'success' => Campaign::STATUS_SENT,
                    'danger' => Campaign::STATUS_FAILED,
                ]),
                TextColumn::make('scheduled_at')->dateTime()->placeholder('—'),
                TextColumn::make('total_recipients')->label('Recipients')->sortable(),
                TextColumn::make('sent_count')->label('Sent')->sortable(),
                TextColumn::make('failed_count')->label('Failed')->sortable(),
                TextColumn::make('created_at')->dateTime()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Action::make('sendNow')
                    ->label('Send Now')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->visible(fn (Campaign $record) => $record->status === Campaign::STATUS_DRAFT)
                    ->requiresConfirmation()
                    ->modalDescription(fn (Campaign $record) => 'This will send to '.$record->audienceQuery()->count().' recipient(s) now. This cannot be undone.')
                    ->action(fn (Campaign $record) => app(DispatchCampaignAction::class)->execute($record)),
                EditAction::make()
                    ->visible(fn (Campaign $record) => $record->status === Campaign::STATUS_DRAFT),
                DeleteAction::make()
                    ->visible(fn (Campaign $record) => in_array($record->status, [Campaign::STATUS_DRAFT, Campaign::STATUS_SCHEDULED], true)),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            CampaignDeliveriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCampaigns::route('/'),
            'create' => Pages\CreateCampaign::route('/create'),
            'edit' => Pages\EditCampaign::route('/{record}/edit'),
        ];
    }
}
