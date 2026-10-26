<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class VerificacionCorreo extends Mailable
{
    public string $code;

    public function __construct(string $code)
    {
        $this->code = $code;
    }

    public function envelope(): \Illuminate\Mail\Mailables\Envelope
    {
        return new \Illuminate\Mail\Mailables\Envelope(
            subject: 'Verificación de cuenta'
        );
    }

    public function content(): \Illuminate\Mail\Mailables\Content
    {
        return new \Illuminate\Mail\Mailables\Content(
            view: 'emails.verificacion',
            with: [
                'code' => $this->code,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}