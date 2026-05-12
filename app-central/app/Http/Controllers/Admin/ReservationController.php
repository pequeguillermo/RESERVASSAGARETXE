<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Inertia\Inertia;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function index()
    {
        $reservations = Reservation::with('member')->orderBy('date', 'desc')->get();
        
        // Agrupar estadísticas de clientes por teléfono
        $clientStats = Reservation::selectRaw('phone, COUNT(*) as total_reservations, SUM(CASE WHEN status IN ("cancelada_tlf", "cancelada_mail") THEN 1 ELSE 0 END) as total_cancellations, MAX(date) as last_reservation')
            ->groupBy('phone')
            ->get()
            ->keyBy('phone');

        // Mapear las reservas con sus estadísticas
        $reservations->map(function ($reservation) use ($clientStats) {
            $reservation->client_stats = $clientStats[$reservation->phone] ?? null;
            return $reservation;
        });

        // Cargar datos para las pestañas de configuración de reservas
        $settings = \App\Models\Setting::all()->pluck('value', 'key');
        
        $schedules = \App\Models\Schedule::orderBy('day_of_week')->get();
        if ($schedules->count() < 7) {
            for ($i = 0; $i <= 6; $i++) {
                \App\Models\Schedule::firstOrCreate(['day_of_week' => $i]);
            }
            $schedules = \App\Models\Schedule::orderBy('day_of_week')->get();
        }

        $specialSchedules = \App\Models\SpecialSchedule::orderBy('date', 'desc')->get();

        $members = \App\Models\Member::withCount('reservations')->orderBy('created_at', 'desc')->get();

        return Inertia::render('Reservations', [
            'reservations' => $reservations,
            'settings' => $settings,
            'schedules' => $schedules,
            'special_schedules' => $specialSchedules,
            'members' => $members
        ]);
    }

    public function update(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'people' => 'required|integer|min:1',
            'status' => 'sometimes|string|in:pendiente,confirmada,cancelada_tlf,cancelada_mail'
        ]);
        
        $reservation->update($validated);
        return redirect()->back()->with('success', 'Reserva actualizada correctamente');
    }

    public function cancel(Reservation $reservation)
    {
        $reservation->update(['status' => 'cancelada_tlf']);
        
        if ($reservation->email) {
            \Illuminate\Support\Facades\Mail::to($reservation->email)->send(new \App\Mail\ReservationCancelled($reservation));
        }
        
        return redirect()->back()->with('success', 'Reserva cancelada correctamente');
    }
}
