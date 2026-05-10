<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Reservation;
use App\Models\Setting;

class ReservationFeedback extends Mailable
{
    use Queueable, SerializesModels;

    public $reservation;

    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¿Qué tal tu experiencia en Sagaretxe?',
        );
    }

    public function build()
    {
        $template = Setting::where('key', 'email_feedback_24h')->value('value') 
            ?? 'Hola [nombre], esperamos que hayas disfrutado tu comida ayer. ¡Déjanos una reseña en Google!';
            
        $body = str_replace(
            ['[nombre]', '[fecha]', '[hora]', '[comensales]'],
            [
                $this->reservation->name,
                $this->reservation->date->format('d/m/Y'),
                $this->reservation->date->format('H:i'),
                $this->reservation->people
            ],
            $template
        );

        return $this->html(nl2br(htmlspecialchars($body)));
    }
}
