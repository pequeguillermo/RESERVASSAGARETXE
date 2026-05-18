<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Reservation;
use App\Models\Setting;

class ReservationCancelled extends Mailable
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
            subject: 'Cancelación de tu reserva en Sagaretxe',
        );
    }

    public function build()
    {
        $template = Setting::where('key', 'email_cancellation')->value('value') 
            ?? 'Hola [nombre], tu reserva para el día [fecha] a las [hora] para [comensales] personas ha sido cancelada.';
            
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

        return $this->html(nl2br($body));
    }
}
