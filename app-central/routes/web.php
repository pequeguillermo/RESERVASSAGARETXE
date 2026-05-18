<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/clear-cache', function () {
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    return 'Caché de configuración limpiada con éxito en el servidor. Vuelve a probar ahora.';
});

Route::get('/test-mail', function (\Illuminate\Http\Request $request) {
    try {
        $to = $request->get('to', 'admin@sagaretxe.com');
        \Illuminate\Support\Facades\Mail::raw('Prueba de conexión Resend desde la API de Sagaretxe', function ($message) use ($to) {
            $message->to($to)
                    ->subject('Prueba SMTP Sagaretxe ' . date('Y-m-d H:i:s'));
        });
        return "¡Email enviado correctamente a {$to}! Si no llega, revisa la bandeja de spam. En Resend debería aparecer en logs.";
    } catch (\Throwable $e) {
        return 'Error FATAL al enviar el email: ' . $e->getMessage();
    }
});

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/reservations', [\App\Http\Controllers\Admin\ReservationController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('reservations.index');
Route::post('/reservations', [\App\Http\Controllers\Admin\ReservationController::class, 'store'])
    ->middleware(['auth', 'verified'])->name('reservations.store');
Route::put('/reservations/{reservation}', [\App\Http\Controllers\Admin\ReservationController::class, 'update'])
    ->middleware(['auth', 'verified'])->name('reservations.update');
Route::post('/reservations/{reservation}/cancel', [\App\Http\Controllers\Admin\ReservationController::class, 'cancel'])
    ->middleware(['auth', 'verified'])->name('reservations.cancel');

Route::get('/club', [\App\Http\Controllers\Admin\ClubController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('club');
Route::post('/club', [\App\Http\Controllers\Admin\ClubController::class, 'store'])
    ->middleware(['auth', 'verified'])->name('club.store');
Route::delete('/club/{member}', [\App\Http\Controllers\Admin\ClubController::class, 'destroy'])
    ->middleware(['auth', 'verified'])->name('club.destroy');

Route::get('/customers', [\App\Http\Controllers\Admin\CustomerController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('customers.index');
Route::post('/customers', [\App\Http\Controllers\Admin\CustomerController::class, 'store'])
    ->middleware(['auth', 'verified'])->name('customers.store');

Route::middleware(['auth', 'verified', \App\Http\Middleware\EnsureSuperadmin::class])->group(function () {
    Route::get('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'updateSettings'])->name('settings.update');
    Route::post('/settings/schedules', [\App\Http\Controllers\Admin\SettingsController::class, 'updateSchedules'])->name('settings.schedules');
    Route::post('/settings/special-schedules', [\App\Http\Controllers\Admin\SettingsController::class, 'storeSpecialSchedule'])->name('settings.special.store');
    Route::post('/settings/special-schedules/quick-close', [\App\Http\Controllers\Admin\SettingsController::class, 'quickCloseShift'])->name('settings.special.quick-close');
    Route::delete('/settings/special-schedules/{special_schedule}', [\App\Http\Controllers\Admin\SettingsController::class, 'destroySpecialSchedule'])->name('settings.special.destroy');

    Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::post('/users', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
