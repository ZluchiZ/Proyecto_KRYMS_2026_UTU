<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class VerificacionCorreo extends Mailable
{
<<<<<<< HEAD
    public string $code;

    public function __construct(string $code)
    {
        $this->code = $code;
=======
    public $token;

    public function __construct($token)
    {
        $this->token = $token;
>>>>>>> 2454183e5acfdf3e4c0d75ce3f8717a428fe5a8f
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
<<<<<<< HEAD
                'code' => $this->code,
=======
                'token' => $this->token
>>>>>>> 2454183e5acfdf3e4c0d75ce3f8717a428fe5a8f
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}