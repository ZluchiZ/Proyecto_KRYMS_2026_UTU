<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordRecoveryRequested extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $accountEmail,
        public string $accountType,
        public string $identifier,
        public string $dashboardUrl,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.from.address', 'hello@example.com'),
                config('mail.from.name', config('app.name', 'Laravel')),
            ),
            subject: 'Solicitud de recuperación de contraseña',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.password-recovery-requested');
    }
}