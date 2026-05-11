<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\ValidationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Members
Route::post('/members', [MemberController::class, 'store']);
Route::get('/members/check', [MemberController::class, 'check']);
Route::get('/members/validate', [MemberController::class, 'validateQr']);
Route::put('/members/{member}', [MemberController::class, 'update']);

// Reservations
Route::post('/reservations', [ReservationController::class, 'store']);
Route::get('/reservations/{reservation}/confirm', [ReservationController::class, 'confirm'])->name('api.reservations.confirm')->middleware('signed');
Route::get('/reservations/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('api.reservations.cancel')->middleware('signed');
Route::get('/schedules/availability', [\App\Http\Controllers\Api\ScheduleController::class, 'availability']);

// Validations
Route::post('/validations', [ValidationController::class, 'store']);
