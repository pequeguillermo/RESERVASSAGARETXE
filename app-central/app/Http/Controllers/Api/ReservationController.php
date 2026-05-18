<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Reservation;
use App\Mail\ReservationConfirmed;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ReservationController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'date' => 'required|date',
            'people' => 'required|integer|min:1',
            'adults' => 'nullable|integer|min:0',
            'children' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
            'email' => 'nullable|email',
            'allergies' => 'boolean',
            'celiac' => 'boolean',
            'strollers' => 'boolean',
            'reduced_mobility' => 'boolean',
            'wheelchairs' => 'boolean'
        ]);

        $dateObj = \Carbon\Carbon::parse($request->date);
        $dateStr = $dateObj->toDateString();
        
        $special = \App\Models\SpecialSchedule::where('date', $dateStr)->first();
        if ($special && $special->is_closed) {
            if ($special->max_diners > 0) {
                $currentDiners = Reservation::whereDate('date', $dateStr)
                    ->whereNotIn('status', ['cancelada_tlf', 'cancelada_mail'])
                    ->sum('people');
                
                if (($currentDiners + $request->people) > $special->max_diners) {
                    return response()->json([
                        'message' => 'Lamentamos informar que para ese día solo aceptamos un máximo de ' . $special->max_diners . ' personas y se superaría el límite.',
                    ], 422);
                }
            } else {
                return response()->json([
                    'message' => 'Lamentamos informar que estamos cerrados en la fecha/hora seleccionada.',
                ], 422);
            }
        }

        $member = Member::where('phone', $request->phone)->first();
        $discountApplied = $member !== null;

        $reservation = Reservation::create([
            'member_id' => $member ? $member->id : null,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'date' => $request->date,
            'people' => $request->people,
            'adults' => $request->adults ?? 0,
            'children' => $request->children ?? 0,
            'allergies' => $request->allergies ?? false,
            'celiac' => $request->celiac ?? false,
            'strollers' => $request->strollers ?? false,
            'reduced_mobility' => $request->reduced_mobility ?? false,
            'wheelchairs' => $request->wheelchairs ?? false,
            'notes' => $request->notes,
            'discount_applied' => $discountApplied,
            'status' => 'pendiente'
        ]);

        if ($request->filled('email')) {
            try {
                Mail::to($request->email)->send(new ReservationConfirmed($reservation));
            } catch (\Throwable $e) {
                // Log email error, but do not fail the reservation
                \Log::error('Error sending reservation email: ' . $e->getMessage());
            }
        }

        // Notificación al administrador
        $adminEmail = Setting::where('key', 'admin_email')->value('value');
        if ($adminEmail) {
            try {
                $subjectTpl = Setting::where('key', 'subject_admin_reservation')->value('value') ?? '🔔 Nueva Reserva Recibida';
                $bodyTpl = Setting::where('key', 'email_admin_reservation')->value('value') 
                    ?? "Se ha recibido una nueva reserva:\n\nCliente: [nombre]\nFecha: [fecha]\nHora: [hora]\nComensales: [comensales]\nTeléfono: [telefono]\nEmail: [email]\nNecesidades: [necesidades]\nNotas: [notas]";

                $necesidades = [];
                if ($reservation->allergies) $necesidades[] = 'Alergias';
                if ($reservation->celiac) $necesidades[] = 'Celíacos';
                if ($reservation->strollers) $necesidades[] = 'Carritos de bebé';
                if ($reservation->reduced_mobility) $necesidades[] = 'Movilidad reducida';
                if ($reservation->wheelchairs) $necesidades[] = 'Silla de ruedas';
                $necesidadesStr = empty($necesidades) ? 'Ninguna' : implode(', ', $necesidades);
                
                $notasStr = $reservation->notes ?: 'Ninguna';

                $dateObj2 = \Carbon\Carbon::parse($reservation->date);
                $body = str_replace(
                    ['[nombre]', '[fecha]', '[hora]', '[comensales]', '[telefono]', '[email]', '[necesidades]', '[notas]'],
                    [$reservation->name, $dateObj2->format('d/m/Y'), $dateObj2->format('H:i'), $reservation->people, $reservation->phone, $reservation->email ?? '-', $necesidadesStr, $notasStr],
                    $bodyTpl
                );
                $subject = str_replace(
                    ['[nombre]', '[fecha]', '[hora]', '[comensales]'],
                    [$reservation->name, $dateObj2->format('d/m/Y'), $dateObj2->format('H:i'), $reservation->people],
                    $subjectTpl
                );

                Mail::to($adminEmail)->send(new \App\Mail\AdminNotification($subject, $body));
            } catch (\Throwable $e) {
                \Log::error('Error sending admin notification: ' . $e->getMessage());
            }
        }

        return response()->json([
            'message' => 'Reserva creada con éxito',
            'reservation_id' => $reservation->id,
            'discount_applied' => $discountApplied,
        ]);
    }

    public function confirm(Request $request, Reservation $reservation)
    {
        if ($reservation->status === 'pendiente') {
            $reservation->update(['status' => 'confirmada']);
        }
        
        $redirectUrl = Setting::where('key', 'url_confirm_redirect')->value('value');
        
        if ($redirectUrl) {
            return redirect()->away($redirectUrl);
        }
        
        return response('Reserva confirmada. Gracias.', 200);
    }

    public function cancel(Request $request, Reservation $reservation)
    {
        if (in_array($reservation->status, ['pendiente', 'confirmada'])) {
            $reservation->update(['status' => 'cancelada_mail']);
        }

        $redirectUrl = Setting::where('key', 'url_cancel_redirect')->value('value');
        
        if ($redirectUrl) {
            return redirect()->away($redirectUrl);
        }
        
        return response('Reserva cancelada. Lamentamos que no puedas venir.', 200);
    }
}
