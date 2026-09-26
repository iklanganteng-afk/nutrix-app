<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $otp;
    public string $action;
    public string $recipientName;

    /**
     * Create a new message instance.
     */
    public function __construct(string $otp, string $action = 'login', string $recipientName = 'User')
    {
        $this->otp = $otp;
        $this->action = $action;
        $this->recipientName = $recipientName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->action === 'register' 
            ? '🔐 [NUTRIX] Kode Verifikasi Pendaftaran Akun' 
            : '🔐 [NUTRIX] Kode Verifikasi Masuk (Login)';

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.otp',
            with: [
                'otp' => $this->otp,
                'action' => $this->action,
                'name' => $this->recipientName,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
