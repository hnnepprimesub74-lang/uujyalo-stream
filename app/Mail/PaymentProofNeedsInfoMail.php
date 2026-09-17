<?php

namespace App\Mail;

use App\Models\PaymentProof;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentProofNeedsInfoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public PaymentProof $paymentProof)
    {
    }

    public function build(): self
    {
        return $this->subject('We need more info about your payment')
            ->markdown('emails.payment-needs-info', [
                'paymentProof' => $this->paymentProof,
            ]);
    }
}
