<?php

namespace App\Jobs;

use App\Mail\CampaignMail;
use App\Models\Campaign;
use App\Models\CampaignDelivery;
use App\Models\User;
use App\Services\Sms\SmsChannelInterface;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendCampaignChunk implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param  array<int, int>  $userIds
     */
    public function __construct(public Campaign $campaign, public array $userIds)
    {
    }

    public function handle(SmsChannelInterface $sms): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $users = User::query()->whereIn('id', $this->userIds)->get();

        $sent = 0;
        $failed = 0;

        foreach ($users as $user) {
            if ($this->campaign->send_email) {
                if ($this->sendEmail($user)) {
                    $sent++;
                } else {
                    $failed++;
                }
            }

            if ($this->campaign->send_sms) {
                if ($this->sendSms($sms, $user)) {
                    $sent++;
                } else {
                    $failed++;
                }
            }
        }

        if ($sent > 0) {
            $this->campaign->increment('sent_count', $sent);
        }

        if ($failed > 0) {
            $this->campaign->increment('failed_count', $failed);
        }
    }

    protected function sendEmail(User $user): bool
    {
        try {
            Mail::to($user)->send(new CampaignMail($this->campaign, $user));

            $this->logDelivery($user, CampaignDelivery::CHANNEL_EMAIL, CampaignDelivery::STATUS_SENT);

            return true;
        } catch (Throwable $e) {
            $this->logDelivery($user, CampaignDelivery::CHANNEL_EMAIL, CampaignDelivery::STATUS_FAILED, $e->getMessage());

            return false;
        }
    }

    protected function sendSms(SmsChannelInterface $sms, User $user): bool
    {
        if (empty($user->phone)) {
            $this->logDelivery($user, CampaignDelivery::CHANNEL_SMS, CampaignDelivery::STATUS_FAILED, 'No phone number on file');

            return false;
        }

        $result = $sms->send($user->phone, (string) $this->campaign->sms_body);

        $this->logDelivery(
            $user,
            CampaignDelivery::CHANNEL_SMS,
            $result['success'] ? CampaignDelivery::STATUS_SENT : CampaignDelivery::STATUS_FAILED,
            $result['response']
        );

        // Pace requests to the Aakash SMS API, which has no bulk-send endpoint.
        usleep(300_000);

        return $result['success'];
    }

    protected function logDelivery(User $user, string $channel, string $status, ?string $response = null): void
    {
        CampaignDelivery::query()->updateOrCreate(
            ['campaign_id' => $this->campaign->id, 'user_id' => $user->id, 'channel' => $channel],
            ['status' => $status, 'response' => $response]
        );
    }
}
