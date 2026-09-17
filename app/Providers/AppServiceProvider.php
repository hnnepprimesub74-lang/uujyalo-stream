<?php

namespace App\Providers;

use App\Models\AppSetting;
use App\Services\Sms\AakashSmsChannel;
use App\Services\Sms\SmsChannelInterface;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SmsChannelInterface::class, AakashSmsChannel::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->applyDatabaseMailSettings();
    }

    protected function applyDatabaseMailSettings(): void
    {
        if (! Schema::hasTable('app_settings')) {
            return;
        }

        $host = AppSetting::get('mail_host');

        if (empty($host)) {
            return;
        }

        config([
            'mail.default' => AppSetting::get('mail_mailer', 'smtp'),
            'mail.mailers.smtp.host' => $host,
            'mail.mailers.smtp.port' => AppSetting::get('mail_port', 587),
            'mail.mailers.smtp.username' => AppSetting::get('mail_username'),
            'mail.mailers.smtp.password' => AppSetting::get('mail_password'),
            'mail.mailers.smtp.encryption' => AppSetting::get('mail_encryption', 'tls'),
            'mail.from.address' => AppSetting::get('mail_from_address', config('mail.from.address')),
            'mail.from.name' => AppSetting::get('mail_from_name', config('mail.from.name')),
        ]);
    }
}
