<?php

namespace App\Filament\Pages;

use App\Models\AppSetting;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Subscriptions';

    protected static ?int $navigationSort = 15;

    protected static string $view = 'filament.pages.manage-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'mail_mailer' => AppSetting::get('mail_mailer', config('mail.default')),
            'mail_host' => AppSetting::get('mail_host', config('mail.mailers.smtp.host')),
            'mail_port' => AppSetting::get('mail_port', config('mail.mailers.smtp.port')),
            'mail_username' => AppSetting::get('mail_username', config('mail.mailers.smtp.username')),
            'mail_password' => AppSetting::get('mail_password'),
            'mail_encryption' => AppSetting::get('mail_encryption', config('mail.mailers.smtp.encryption')),
            'mail_from_address' => AppSetting::get('mail_from_address', config('mail.from.address')),
            'mail_from_name' => AppSetting::get('mail_from_name', config('mail.from.name')),
            'aakash_sms_auth_token' => AppSetting::get('aakash_sms_auth_token', config('services.aakash_sms.auth_token')),
            'aakash_sms_url' => AppSetting::get('aakash_sms_url', config('services.aakash_sms.url')),
            'reminder_days_before_expiry' => AppSetting::get('reminder_days_before_expiry', 3),
            'payment_instructions' => AppSetting::get('payment_instructions', "Bank Transfer: Account Name - Uujyalo Stream\nAccount No: 0000000000\nBank: Your Bank\n\nOr eSewa/Khalti Wallet: 98XXXXXXXX\n\nAfter payment, submit the reference number and screenshot below."),
            'payment_qr_code' => AppSetting::get('payment_qr_code'),
            'whatsapp_number' => AppSetting::get('whatsapp_number'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Email (SMTP) Settings')
                ->columns(2)
                ->schema([
                    TextInput::make('mail_mailer')->label('Mailer')->default('smtp'),
                    TextInput::make('mail_host')->label('SMTP Host'),
                    TextInput::make('mail_port')->label('SMTP Port')->numeric(),
                    TextInput::make('mail_encryption')->label('Encryption (tls/ssl)'),
                    TextInput::make('mail_username')->label('SMTP Username'),
                    TextInput::make('mail_password')->label('SMTP Password')->password()->revealable(),
                    TextInput::make('mail_from_address')->label('From Address')->email(),
                    TextInput::make('mail_from_name')->label('From Name'),
                ]),
            Section::make('Aakash SMS Settings')
                ->columns(2)
                ->schema([
                    TextInput::make('aakash_sms_auth_token')->label('Auth Token')->password()->revealable(),
                    TextInput::make('aakash_sms_url')->label('API URL')->default('https://sms.aakashsms.com/sms/v3/send'),
                ]),
            Section::make('Reminder Timing')
                ->schema([
                    TextInput::make('reminder_days_before_expiry')
                        ->label('Send "expiring soon" reminder this many days before expiry')
                        ->numeric()
                        ->minValue(1)
                        ->default(3),
                ]),
            Section::make('Payment Instructions')
                ->description('Shown to customers on the subscribe page.')
                ->schema([
                    Textarea::make('payment_instructions')
                        ->label('Instructions')
                        ->rows(6)
                        ->columnSpanFull(),
                    FileUpload::make('payment_qr_code')
                        ->label('Payment QR Code')
                        ->image()
                        ->disk('public')
                        ->directory('payment-qr')
                        ->helperText('Shown to customers alongside the instructions on the payment page.')
                        ->columnSpanFull(),
                ]),
            Section::make('Storefront')
                ->description('Shown to customers on the storefront (uujyalostream.com).')
                ->schema([
                    TextInput::make('whatsapp_number')
                        ->label('WhatsApp Number')
                        ->helperText('Include country code, e.g. 9779800000000. Leave blank to hide the WhatsApp button.')
                        ->tel(),
                ]),
        ])->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            AppSetting::set($key, $value);
        }

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }
}
