<?php

namespace App\Mail;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Subscription $subscription)
    {
    }

    public function build(): self
    {
        return $this->subject('Your account is updated — '.$this->subscription->plan->product?->name)
            ->markdown('emails.account-updated', [
                'subscription' => $this->subscription,
            ]);
    }
}
