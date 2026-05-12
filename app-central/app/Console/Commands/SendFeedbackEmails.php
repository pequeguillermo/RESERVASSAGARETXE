<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reservation;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationFeedback;
use Carbon\Carbon;

class SendFeedbackEmails extends Command
{
    protected $signature = 'reservations:send-feedback';
    protected $description = 'Send feedback emails to reservations 24 hours after';

    public function handle()
    {
        $start = Carbon::now()->subHours(24)->startOfHour();
        $end = Carbon::now()->subHours(24)->endOfHour();

        // Podríamos pasarlas a completadas
        $reservations = Reservation::whereIn('status', ['confirmada', 'realizada'])
            ->whereBetween('date', [$start, $end])
            ->whereNotNull('email')
            ->get();

        foreach ($reservations as $reservation) {
            Mail::to($reservation->email)->send(new ReservationFeedback($reservation));
            $reservation->update(['status' => 'realizada']);
            $this->info('Feedback sent to: ' . $reservation->email);
        }

        $this->info('Finished sending feedback.');
    }
}
