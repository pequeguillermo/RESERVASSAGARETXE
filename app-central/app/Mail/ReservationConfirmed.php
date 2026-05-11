<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Reservation;
use App\Models\Setting;

class ReservationConfirmed extends Mailable
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
            subject: 'Confirmación de tu reserva en Sagaretxe',
        );
    }

    public function build()
    {
        $template = Setting::where('key', 'email_confirmation')->value('value') 
            ?? 'Hola [nombre], tu reserva para el día [fecha] a las [hora] para [comensales] personas ha sido confirmada.';
            
        $confirmUrl = \Illuminate\Support\Facades\URL::signedRoute('api.reservations.confirm', ['reservation' => $this->reservation->id]);
        $cancelUrl = \Illuminate\Support\Facades\URL::signedRoute('api.reservations.cancel', ['reservation' => $this->reservation->id]);

        $confirmBtn = '<a href="'.$confirmUrl.'" style="display:inline-block;padding:10px 20px;background-color:#4f46e5;color:white;text-decoration:none;border-radius:6px;font-weight:bold;">Confirmar Reserva</a>';
        $cancelBtn = '<a href="'.$cancelUrl.'" style="display:inline-block;padding:10px 20px;background-color:#ef4444;color:white;text-decoration:none;border-radius:6px;font-weight:bold;">Cancelar Reserva</a>';

        $body = str_replace(
            ['[nombre]', '[fecha]', '[hora]', '[comensales]', '[confirmar]', '[cancelar]'],
            [
                $this->reservation->name,
                $this->reservation->date->format('d/m/Y'),
                $this->reservation->date->format('H:i'),
                $this->reservation->people,
                $confirmBtn,
                $cancelBtn
            ],
            $template
        );

        return $this->html(nl2br(htmlspecialchars($body)));
    }
}
