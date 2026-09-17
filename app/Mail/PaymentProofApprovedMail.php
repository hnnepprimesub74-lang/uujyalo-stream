<?php

namespace App\Mail;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentProofApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Subscription $subscription)
    {
    }

    public function build(): self
    {
        return $this->subject('Your subscription has been activated')
            ->markdown('emails.payment-approved', [
                'subscription' => $this->subscription,
            ]);
    }
}
