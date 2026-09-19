<?php

namespace App\Mail;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountReadyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Subscription $subscription)
    {
    }

    public function build(): self
    {
        return $this->subject('Your account is ready — '.$this->subscription->plan->product?->name)
            ->markdown('emails.account-ready', [
                'subscription' => $this->subscription,
            ]);
    }
}
