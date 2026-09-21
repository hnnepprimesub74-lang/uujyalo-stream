<?php

namespace App\Jobs;

use App\Jobs\Concerns\PersonalizesSmsBody;
use App\Mail\CampaignMail;
use App\Models\Campaign;
use App\Models\CampaignDelivery;
use App\Models\MarketingContact;
use App\Services\Sms\SmsChannelInterface;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Throwable;

/**
 * Mirrors SendCampaignChunk but for imported MarketingContact recipients
 * rather than registered Users — kept as a separate job since the two
 * recipient types have different models, unsubscribe routes, and no
 * "active customer" concept.
 */
class SendCampaignContactChunk implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels, PersonalizesSmsBody;

    /**
     * @param  array<int, int>  $contactIds
     */
    public function __construct(public Campaign $campaign, public array $contactIds)
    {
    }

    public function handle(SmsChannelInterface $sms): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $contacts = MarketingContact::query()->whereIn('id', $this->contactIds)->get();

        $sent = 0;
        $failed = 0;

        foreach ($contacts as $contact) {
            if ($this->campaign->send_email) {
                if ($this->sendEmail($contact)) {
                    $sent++;
                } else {
                    $failed++;
                }
            }

            if ($this->campaign->send_sms) {
                if ($this->sendSms($sms, $contact)) {
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

    protected function sendEmail(MarketingContact $contact): bool
    {
        if (empty($contact->email)) {
            $this->logDelivery($contact, CampaignDelivery::CHANNEL_EMAIL, CampaignDelivery::STATUS_FAILED, 'No email on file');

            return false;
        }

        try {
            $unsubscribeUrl = URL::signedRoute('marketing.unsubscribe.contact', ['contact' => $contact->id]);

            Mail::to($contact->email)->send(new CampaignMail($this->campaign, $contact->name ?? $contact->email, $unsubscribeUrl));

            $this->logDelivery($contact, CampaignDelivery::CHANNEL_EMAIL, CampaignDelivery::STATUS_SENT);

            return true;
        } catch (Throwable $e) {
            $this->logDelivery($contact, CampaignDelivery::CHANNEL_EMAIL, CampaignDelivery::STATUS_FAILED, $e->getMessage());

            return false;
        }
    }

    protected function sendSms(SmsChannelInterface $sms, MarketingContact $contact): bool
    {
        if (empty($contact->phone)) {
            $this->logDelivery($contact, CampaignDelivery::CHANNEL_SMS, CampaignDelivery::STATUS_FAILED, 'No phone number on file');

            return false;
        }

        $message = $this->personalizeSms((string) $this->campaign->sms_body, $contact->name);
        $result = $sms->send($contact->phone, $message);

        $this->logDelivery(
            $contact,
            CampaignDelivery::CHANNEL_SMS,
            $result['success'] ? CampaignDelivery::STATUS_SENT : CampaignDelivery::STATUS_FAILED,
            $result['response']
        );

        // Pace requests to the Aakash SMS API, which has no bulk-send endpoint.
        usleep(300_000);

        return $result['success'];
    }

    protected function logDelivery(MarketingContact $contact, string $channel, string $status, ?string $response = null): void
    {
        CampaignDelivery::query()->updateOrCreate(
            ['campaign_id' => $this->campaign->id, 'contact_id' => $contact->id, 'channel' => $channel],
            ['status' => $status, 'response' => $response]
        );
    }
}
