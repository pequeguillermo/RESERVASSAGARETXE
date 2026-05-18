<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Reservation;
use App\Models\Setting;

class ReservationFinalConfirmed extends Mailable
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
            subject: '¡Tu reserva en Sagaretxe ha sido confirmada con éxito!',
        );
    }

    public function build()
    {
        $template = Setting::where('key', 'email_final_confirmation')->value('value') 
            ?? 'Hola [nombre], gracias por confirmar. Tu reserva para el día [fecha] a las [hora] para [comensales] personas ha sido confirmada.';
            
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
