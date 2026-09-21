<?php

namespace App\Filament\Resources;

use App\Actions\DispatchCampaignAction;
use App\Filament\Resources\CampaignResource\Pages;
use App\Filament\Resources\CampaignResource\RelationManagers\CampaignDeliveriesRelationManager;
use App\Models\Campaign;
use App\Models\MarketingContact;
use App\Models\User;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Radio;
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
            Radio::make('campaign_type')
                ->label('Campaign Type')
                ->options([
                    'email' => 'Email Campaign',
                    'sms' => 'SMS Campaign',
                ])
                ->default('email')
                ->required()
                ->live()
                ->inline()
                ->columnSpanFull(),

            TextInput::make('subject')
                ->label('Email Subject')
                ->visible(fn ($get) => $get('campaign_type') === 'email')
                ->required(fn ($get) => $get('campaign_type') === 'email')
                ->maxLength(255)
                ->columnSpanFull(),
            Textarea::make('email_body')
                ->label('Email Body')
                ->helperText('Supports basic markdown (e.g. **bold**, [link](https://...)).')
                ->rows(6)
                ->visible(fn ($get) => $get('campaign_type') === 'email')
                ->required(fn ($get) => $get('campaign_type') === 'email')
                ->columnSpanFull(),

            Textarea::make('sms_body')
                ->label('SMS Text')
                ->rows(3)
                ->live()
                ->visible(fn ($get) => $get('campaign_type') === 'sms')
                ->required(fn ($get) => $get('campaign_type') === 'sms')
                ->hintAction(
                    FormAction::make('insertFirstName')
                        ->label('Insert First Name')
                        ->icon('heroicon-m-user')
                        ->action(function (callable $set, callable $get) {
                            $set('sms_body', rtrim((string) $get('sms_body')).' {first_name}');
                        })
                )
                ->helperText(function ($get) {
                    $length = strlen((string) $get('sms_body'));
                    $segments = max(1, (int) ceil($length / 160));

                    return "Use {first_name} to insert the recipient's first name (falls back to \"there\" if unknown). {$length} characters — {$segments} SMS segment(s).";
                })
                ->columnSpanFull(),

            Select::make('audience_filter')
                ->label('Audience')
                ->options([
                    Campaign::AUDIENCE_ALL => 'All Customers',
                    Campaign::AUDIENCE_ACTIVE_SUBSCRIBERS => 'Active Customers (has an active subscription)',
                    Campaign::AUDIENCE_INACTIVE => 'Inactive Customers (no active subscription)',
                    Campaign::AUDIENCE_BY_PRODUCT => 'Customers of a Specific Product',
                ])
                ->default(Campaign::AUDIENCE_ALL)
                ->required()
                ->live()
                ->native(false),
            Select::make('audience_product_id')
                ->label('Product')
                ->relationship('product', 'name')
                ->visible(fn ($get) => $get('audience_filter') === Campaign::AUDIENCE_BY_PRODUCT)
                ->required(fn ($get) => $get('audience_filter') === Campaign::AUDIENCE_BY_PRODUCT)
                ->searchable()
                ->preload(),
            Select::make('excluded_user_ids')
                ->label('Exclude Specific Customers')
                ->helperText('Search by name or email. These customers are skipped even if they match the audience above.')
                ->multiple()
                ->searchable()
                ->getSearchResultsUsing(fn (string $search) => User::query()
                    ->where('role', User::ROLE_CUSTOMER)
                    ->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                    ->limit(50)
                    ->pluck('email', 'id')
                    ->all())
                ->getOptionLabelsUsing(fn (array $values) => User::query()->whereIn('id', $values)->pluck('email', 'id')->all())
                ->columnSpanFull(),

            Toggle::make('include_contacts')
                ->label('Also Send To Imported Contact List')
                ->live()
                ->helperText(fn () => number_format(MarketingContact::query()->where('marketing_opt_out', false)->count()).' contacts available (manage under Marketing → Contacts).')
                ->columnSpanFull(),
            TextInput::make('contact_range_start')
                ->label('From #')
                ->numeric()
                ->minValue(1)
                ->placeholder('1')
                ->visible(fn ($get) => $get('include_contacts'))
                ->helperText('Position in the imported list (by import order), not the phone number itself.'),
            TextInput::make('contact_range_end')
                ->label('To #')
                ->numeric()
                ->minValue(1)
                ->gte('contact_range_start')
                ->placeholder(fn () => number_format(MarketingContact::query()->where('marketing_opt_out', false)->count()))
                ->visible(fn ($get) => $get('include_contacts'))
                ->helperText('Leave both blank to send to the entire imported list.'),

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
                TextColumn::make('subject')
                    ->label('Subject / Type')
                    ->searchable()
                    ->limit(40)
                    ->formatStateUsing(fn (Campaign $record) => $record->send_sms ? '(SMS Campaign)' : ($record->subject ?: '—')),
                TextColumn::make('channels')
                    ->label('Channel')
                    ->badge()
                    ->state(fn (Campaign $record) => $record->send_sms ? 'SMS' : 'Email'),
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
                    ->modalDescription(function (Campaign $record) {
                        $count = $record->audienceQuery()->count();

                        if ($record->include_contacts) {
                            $count += count($record->contactsToSendIds());
                        }

                        return "This will send to {$count} recipient(s) now. This cannot be undone.";
                    })
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
