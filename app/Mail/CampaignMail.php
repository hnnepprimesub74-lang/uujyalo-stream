<?php

namespace App\Mail;

use App\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Deliberately NOT ShouldQueue: it's sent synchronously from inside
 * SendCampaignChunk/SendCampaignContactChunk, which are already the async
 * unit of work for a batch of recipients. If this implemented ShouldQueue,
 * Laravel's Mailer::send() would auto-requeue it instead of sending
 * immediately, which would make the chunk job's delivery log ("sent")
 * inaccurate — it would only mean "queued", not delivered.
 *
 * Takes a plain recipient name/email rather than a User model so it can be
 * sent to either a registered customer or an imported MarketingContact.
 */
class CampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Campaign $campaign,
        public string $recipientName,
        public string $unsubscribeUrl,
    ) {
    }

    public function build(): self
    {
        return $this->subject($this->campaign->subject)
            ->markdown('emails.campaign', [
                'campaign' => $this->campaign,
                'recipientName' => $this->recipientName,
                'unsubscribeUrl' => $this->unsubscribeUrl,
            ]);
    }
}
