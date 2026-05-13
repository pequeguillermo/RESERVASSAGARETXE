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
    public function __construct()
    {
        // Solo superadmin puede acceder a la configuración de correos y horarios
    }

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

        $specialSchedules = SpecialSchedule::where(function($query) {
            $query->where('date', '>=', now()->toDateString())
                  ->orWhere('is_permanent', true);
        })->orderBy('date', 'desc')->get();

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
            'is_closed' => 'boolean',
            'is_permanent' => 'boolean',
            'close_morning' => 'boolean',
            'close_afternoon' => 'boolean',
            'max_diners' => 'nullable|integer|min:0'
        ]);

        $dateObj = \Carbon\Carbon::parse($validated['date']);
        $regular = Schedule::where('day_of_week', $dateObj->dayOfWeek)->first();

        $data = [
            'is_closed' => $validated['is_closed'] ?? false,
            'is_permanent' => $validated['is_permanent'] ?? false,
            'open_time' => (!empty($validated['is_closed']) || !empty($validated['close_morning'])) ? null : ($regular ? $regular->open_time : null),
            'close_time' => (!empty($validated['is_closed']) || !empty($validated['close_morning'])) ? null : ($regular ? $regular->close_time : null),
            'open_time_2' => (!empty($validated['is_closed']) || !empty($validated['close_afternoon'])) ? null : ($regular ? $regular->open_time_2 : null),
            'close_time_2' => (!empty($validated['is_closed']) || !empty($validated['close_afternoon'])) ? null : ($regular ? $regular->close_time_2 : null),
        ];

        if ((!empty($validated['close_morning']) && !empty($validated['close_afternoon'])) || 
            (empty($data['open_time']) && empty($data['close_time']) && empty($data['open_time_2']) && empty($data['close_time_2']))) {
            $data['is_closed'] = true;
        }

        if (isset($validated['max_diners'])) {
            $data['max_diners'] = $validated['max_diners'] > 0 ? $validated['max_diners'] : null;
            if ($data['max_diners'] > 0) {
                // Si permitimos comensales, forzamos a que no esté 100% cerrado internamente
                // O podemos mantener is_closed = true y la API comprueba max_diners.
                // En el plan dijimos is_closed = true y API verifica max_diners.
            } else if ($validated['max_diners'] === 0) {
                $data['is_closed'] = true;
            }
        }

        SpecialSchedule::updateOrCreate(['date' => $validated['date']], $data);

        return redirect()->back()->with('success', 'Día especial guardado');
    }

    public function destroySpecialSchedule(SpecialSchedule $special_schedule)
    {
        $special_schedule->delete();
        return redirect()->back()->with('success', 'Día especial eliminado');
    }

    public function quickCloseShift(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'shift' => 'required|in:morning,afternoon',
            'max_diners' => 'nullable|integer|min:0'
        ]);
        
        $special = SpecialSchedule::where('date', $validated['date'])->first();
        
        if (!$special) {
            $dateObj = \Carbon\Carbon::parse($validated['date']);
            $regular = Schedule::where('day_of_week', $dateObj->dayOfWeek)->first();
            
            $special = new SpecialSchedule([
                'date' => $validated['date'],
                'open_time' => $regular ? $regular->open_time : null,
                'close_time' => $regular ? $regular->close_time : null,
                'open_time_2' => $regular ? $regular->open_time_2 : null,
                'close_time_2' => $regular ? $regular->close_time_2 : null,
                'is_closed' => false,
                'is_permanent' => false
            ]);
        }
        
        if ($validated['shift'] === 'morning') {
            $special->open_time = null;
            $special->close_time = null;
        } else {
            $special->open_time_2 = null;
            $special->close_time_2 = null;
        }
        
        if (empty($special->open_time) && empty($special->close_time) && empty($special->open_time_2) && empty($special->close_time_2)) {
            $special->is_closed = true;
        }

        if (isset($validated['max_diners'])) {
            $special->max_diners = $validated['max_diners'] > 0 ? $validated['max_diners'] : null;
            if ($validated['max_diners'] === 0) {
                $special->is_closed = true;
            }
        }
        
        $special->save();
        
        return redirect()->back()->with('success', 'Turno cerrado correctamente para el ' . $validated['date']);
    }
}
