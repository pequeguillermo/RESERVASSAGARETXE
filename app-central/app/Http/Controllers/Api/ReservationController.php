<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Reservation;
use App\Mail\ReservationConfirmed;
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
            'notes' => 'nullable|string',
            'email' => 'nullable|email'
        ]);

        $member = Member::where('phone', $request->phone)->first();
        $discountApplied = $member !== null;

        $reservation = Reservation::create([
            'member_id' => $member ? $member->id : null,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'date' => $request->date,
            'people' => $request->people,
            'notes' => $request->notes,
            'discount_applied' => $discountApplied,
            'status' => 'confirmed'
        ]);

        if ($request->filled('email')) {
            try {
                Mail::to($request->email)->send(new ReservationConfirmed($reservation));
            } catch (\Exception $e) {
                // Log email error, but do not fail the reservation
                \Log::error('Error sending reservation email: ' . $e->getMessage());
            }
        }

        return response()->json([
            'message' => 'Reserva creada con éxito',
            'reservation_id' => $reservation->id,
            'discount_applied' => $discountApplied,
        ]);
    }
}
