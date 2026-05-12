<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/reservations', [\App\Http\Controllers\Admin\ReservationController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('reservations.index');
Route::put('/reservations/{reservation}', [\App\Http\Controllers\Admin\ReservationController::class, 'update'])
    ->middleware(['auth', 'verified'])->name('reservations.update');
Route::post('/reservations/{reservation}/cancel', [\App\Http\Controllers\Admin\ReservationController::class, 'cancel'])
    ->middleware(['auth', 'verified'])->name('reservations.cancel');

Route::get('/club', function () {
    return Inertia::render('Club', [
        'members' => \App\Models\Member::withCount('reservations')->orderBy('created_at', 'desc')->get(),
        'settings' => \App\Models\Setting::all()->pluck('value', 'key')
    ]);
})->middleware(['auth', 'verified'])->name('club');

Route::get('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('settings.index');
Route::post('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'updateSettings'])
    ->middleware(['auth', 'verified'])->name('settings.update');
Route::post('/settings/schedules', [\App\Http\Controllers\Admin\SettingsController::class, 'updateSchedules'])
    ->middleware(['auth', 'verified'])->name('settings.schedules');
Route::post('/settings/special-schedules', [\App\Http\Controllers\Admin\SettingsController::class, 'storeSpecialSchedule'])
    ->middleware(['auth', 'verified'])->name('settings.special.store');
Route::post('/settings/special-schedules/quick-close', [\App\Http\Controllers\Admin\SettingsController::class, 'quickCloseShift'])
    ->middleware(['auth', 'verified'])->name('settings.special.quick-close');
Route::delete('/settings/special-schedules/{special_schedule}', [\App\Http\Controllers\Admin\SettingsController::class, 'destroySpecialSchedule'])
    ->middleware(['auth', 'verified'])->name('settings.special.destroy');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
