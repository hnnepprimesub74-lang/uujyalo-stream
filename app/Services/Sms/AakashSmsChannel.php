<?php

namespace App\Services\Sms;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class AakashSmsChannel implements SmsChannelInterface
{
    public function send(string $to, string $message): array
    {
        $authToken = AppSetting::get('aakash_sms_auth_token', config('services.aakash_sms.auth_token'));
        $url = AppSetting::get('aakash_sms_url', config('services.aakash_sms.url'));

        if (empty($authToken)) {
            return ['success' => false, 'response' => 'Aakash SMS auth token is not configured.'];
        }

        try {
            $response = Http::asForm()->post($url, [
                'auth_token' => $authToken,
                'to' => $this->normalizePhone($to),
                'text' => $message,
            ]);

            $body = $response->json();
            $success = $response->successful() && empty($body['error']);

            return [
                'success' => $success,
                'response' => $response->body(),
            ];
        } catch (Throwable $e) {
            Log::error('Aakash SMS send failed', ['error' => $e->getMessage(), 'to' => $to]);

            return ['success' => false, 'response' => $e->getMessage()];
        }
    }

    protected function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);

        if (str_starts_with((string) $digits, '977')) {
            $digits = substr($digits, 3);
        }

        return $digits;
    }
}
