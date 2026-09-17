<?php

namespace App\Services;

use App\Mail\SubscriptionExpiredMail;
use App\Mail\SubscriptionExpiringMail;
use App\Models\NotificationLog;
use App\Models\Subscription;
use App\Services\Sms\SmsChannelInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SubscriptionNotifier
{
    public function __construct(protected SmsChannelInterface $sms)
    {
    }

    public function notifyExpiringSoon(Subscription $subscription): void
    {
        $this->sendEmail($subscription, NotificationLog::TYPE_EXPIRING_SOON, new SubscriptionExpiringMail($subscription));
        $this->sendSms(
            $subscription,
            NotificationLog::TYPE_EXPIRING_SOON,
            "Hi {$subscription->user->name}, your {$subscription->plan->name} subscription expires on {$subscription->expires_at->format('M j, Y')}. Renew soon to keep access."
        );
    }

    public function notifyExpired(Subscription $subscription): void
    {
        $this->sendEmail($subscription, NotificationLog::TYPE_EXPIRED, new SubscriptionExpiredMail($subscription));
        $this->sendSms(
            $subscription,
            NotificationLog::TYPE_EXPIRED,
            "Hi {$subscription->user->name}, your {$subscription->plan->name} subscription expired on {$subscription->expires_at->format('M j, Y')}. Renew now to restore access."
        );
    }

    protected function sendEmail(Subscription $subscription, string $type, $mailable): void
    {
        $status = NotificationLog::STATUS_SENT;
        $error = null;

        try {
            Mail::to($subscription->user->email)->send($mailable);
        } catch (Throwable $e) {
            $status = NotificationLog::STATUS_FAILED;
            $error = $e->getMessage();
            Log::error('Subscription email notification failed', ['error' => $error, 'subscription_id' => $subscription->id]);
        }

        NotificationLog::create([
            'user_id' => $subscription->user_id,
            'subscription_id' => $subscription->id,
            'channel' => NotificationLog::CHANNEL_EMAIL,
            'type' => $type,
            'status' => $status,
            'message' => $error,
        ]);
    }

    protected function sendSms(Subscription $subscription, string $type, string $message): void
    {
        if (empty($subscription->user->phone)) {
            NotificationLog::create([
                'user_id' => $subscription->user_id,
                'subscription_id' => $subscription->id,
                'channel' => NotificationLog::CHANNEL_SMS,
                'type' => $type,
                'status' => NotificationLog::STATUS_FAILED,
                'message' => $message,
                'response' => 'No phone number on file.',
            ]);

            return;
        }

        $result = $this->sms->send($subscription->user->phone, $message);

        NotificationLog::create([
            'user_id' => $subscription->user_id,
            'subscription_id' => $subscription->id,
            'channel' => NotificationLog::CHANNEL_SMS,
            'type' => $type,
            'status' => $result['success'] ? NotificationLog::STATUS_SENT : NotificationLog::STATUS_FAILED,
            'message' => $message,
            'response' => $result['response'],
        ]);
    }
}
