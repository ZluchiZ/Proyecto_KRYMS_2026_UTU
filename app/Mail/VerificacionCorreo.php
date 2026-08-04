<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class VerificacionCorreo extends Mailable
{
    public $token;

    public function __construct($token)
    {
        $this->token = $token;
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
                'token' => $this->token
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}