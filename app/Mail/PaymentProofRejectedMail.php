<?php

namespace App\Mail;

use App\Models\PaymentProof;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentProofRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public PaymentProof $paymentProof)
    {
    }

    public function build(): self
    {
        return $this->subject('Your payment could not be verified')
            ->markdown('emails.payment-rejected', [
                'paymentProof' => $this->paymentProof,
            ]);
    }
}
