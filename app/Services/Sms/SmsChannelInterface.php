<?php

namespace App\Services\Sms;

interface SmsChannelInterface
{
    /**
     * Send an SMS message. Returns ['success' => bool, 'response' => string].
     *
     * @return array{success: bool, response: string}
     */
    public function send(string $to, string $message): array;
}
