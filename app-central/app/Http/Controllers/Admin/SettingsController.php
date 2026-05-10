<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\Schedule;
use App\Models\SpecialSchedule;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key');
        
        // Cargar horarios regulares (asegurar que existan los 7 días)
        $schedules = Schedule::orderBy('day_of_week')->get();
        if ($schedules->count() < 7) {
            for ($i = 0; $i <= 6; $i++) {
                Schedule::firstOrCreate(['day_of_week' => $i]);
            }
            $schedules = Schedule::orderBy('day_of_week')->get();
        }

        $specialSchedules = SpecialSchedule::orderBy('date', 'desc')->get();

        return Inertia::render('Settings', [
            'settings' => $settings,
            'schedules' => $schedules,
            'special_schedules' => $specialSchedules
        ]);
    }

    public function updateSettings(Request $request)
    {
        $data = $request->except('_token');
        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        return redirect()->back()->with('success', 'Configuraciones actualizadas');
    }

    public function updateSchedules(Request $request)
    {
        $schedules = $request->input('schedules', []);
        foreach ($schedules as $schedule) {
            Schedule::where('id', $schedule['id'])->update([
                'open_time' => $schedule['open_time'] ?: null,
                'close_time' => $schedule['close_time'] ?: null,
                'open_time_2' => $schedule['open_time_2'] ?: null,
                'close_time_2' => $schedule['close_time_2'] ?: null,
                'is_closed' => $schedule['is_closed']
            ]);
        }
        return redirect()->back()->with('success', 'Horarios guardados');
    }

    public function storeSpecialSchedule(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'open_time' => 'nullable',
            'close_time' => 'nullable',
            'open_time_2' => 'nullable',
            'close_time_2' => 'nullable',
            'is_closed' => 'boolean'
        ]);

        SpecialSchedule::updateOrCreate(['date' => $validated['date']], $validated);
        return redirect()->back()->with('success', 'Día especial guardado');
    }

    public function destroySpecialSchedule(SpecialSchedule $special_schedule)
    {
        $special_schedule->delete();
        return redirect()->back()->with('success', 'Día especial eliminado');
    }
}
