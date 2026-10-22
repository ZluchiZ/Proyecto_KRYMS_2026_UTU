<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class CorreoPrueba extends Mailable
{
    public function build()
    {
        return $this->subject('Correo de prueba')
                    ->view('emails.prueba');
    }
}