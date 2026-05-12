<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Reservation;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReservationReminder;
use Carbon\Carbon;

class SendReminderEmails extends Command
{
    protected $signature = 'reservations:send-reminders';
    protected $description = 'Send reminder emails to reservations 24 hours before';

    public function handle()
    {
        $start = Carbon::now()->addHours(24)->startOfHour();
        $end = Carbon::now()->addHours(24)->endOfHour();

        $reservations = Reservation::where('status', 'confirmada')
            ->whereBetween('date', [$start, $end])
            ->whereNotNull('email')
            ->get();

        foreach ($reservations as $reservation) {
            Mail::to($reservation->email)->send(new ReservationReminder($reservation));
            $this->info('Reminder sent to: ' . $reservation->email);
        }

        $this->info('Finished sending reminders.');
    }
}
