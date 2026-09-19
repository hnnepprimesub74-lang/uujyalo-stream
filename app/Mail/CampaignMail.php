<?php

namespace App\Mail;

use App\Models\Campaign;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

/**
 * Deliberately NOT ShouldQueue: it's sent synchronously from inside
 * SendCampaignChunk, which is already the async unit of work for a batch of
 * recipients. If this implemented ShouldQueue, Laravel's Mailer::send()
 * would auto-requeue it instead of sending immediately, which would make
 * the chunk job's delivery log ("sent") inaccurate — it would only mean
 * "queued", not delivered.
 */
class CampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Campaign $campaign, public User $user)
    {
    }

    public function build(): self
    {
        return $this->subject($this->campaign->subject)
            ->markdown('emails.campaign', [
                'campaign' => $this->campaign,
                'user' => $this->user,
                'unsubscribeUrl' => URL::signedRoute('marketing.unsubscribe', ['user' => $this->user->id]),
            ]);
    }
}
