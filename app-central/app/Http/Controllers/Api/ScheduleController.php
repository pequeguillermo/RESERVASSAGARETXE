<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\SpecialSchedule;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    public function availability(Request $request)
    {
        $dateStr = $request->get('date');
        if (!$dateStr) {
            return response()->json(['error' => 'Date is required'], 400);
        }

        $date = Carbon::parse($dateStr);
        $dayOfWeek = $date->dayOfWeek; // 0 (Sunday) to 6 (Saturday)

        // Comprobar horario especial (Puntual primero, luego Permanente si existe para ese mes/día)
        $special = SpecialSchedule::where(function ($query) use ($date) {
            $query->where('date', $date->format('Y-m-d'))
                  ->orWhere(function ($q) use ($date) {
                      $q->where('is_permanent', true)
                        ->whereRaw('MONTH(date) = ?', [$date->month])
                        ->whereRaw('DAY(date) = ?', [$date->day]);
                  });
        })->orderBy('is_permanent', 'asc')->first();

        if ($special) {
            if ($special->is_closed) {
                return response()->json(['available' => false, 'message' => 'Cerrado por horario especial']);
            }
            return response()->json([
                'available' => true,
                'open_time' => $special->open_time,
                'close_time' => $special->close_time,
                'open_time_2' => $special->open_time_2,
                'close_time_2' => $special->close_time_2,
            ]);
        }

        // Comprobar horario regular
        $regular = Schedule::where('day_of_week', $dayOfWeek)->first();

        if (!$regular || $regular->is_closed) {
            return response()->json(['available' => false, 'message' => 'Cerrado en este día de la semana']);
        }

        return response()->json([
            'available' => true,
            'open_time' => $regular->open_time,
            'close_time' => $regular->close_time,
            'open_time_2' => $regular->open_time_2,
            'close_time_2' => $regular->close_time_2,
        ]);
    }
}
