<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartureController;
use App\Http\Controllers\NeedPartnerController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Public login / logout (PRD section 4.2)
Route::get('login', [AuthController::class, 'showLogin'])->name('login');
Route::post('login', [AuthController::class, 'login'])->name('login.attempt');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

// All internal pages require authentication (PRD section 4.2)
Route::middleware('auth')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('packages', PackageController::class)->except(['show']);

    Route::get('departures', [DepartureController::class, 'index'])->name('departures.index');
    Route::get('departures/create', [DepartureController::class, 'create'])->name('departures.create');
    Route::post('departures', [DepartureController::class, 'store'])->name('departures.store');
    Route::get('departures/{departure}', [DepartureController::class, 'show'])->name('departures.show');
    Route::get('departures/{departure}/edit', [DepartureController::class, 'edit'])->name('departures.edit');
    Route::put('departures/{departure}', [DepartureController::class, 'update'])->name('departures.update');

    Route::post('registrations', [RegistrationController::class, 'store'])->name('registrations.store');
    Route::put('registrations/{registration}', [RegistrationController::class, 'update'])->name('registrations.update');
    Route::delete('registrations/{registration}', [RegistrationController::class, 'destroy'])->name('registrations.destroy');

    Route::get('participants', [ParticipantController::class, 'index'])->name('participants.index');
    Route::get('need-partner', [NeedPartnerController::class, 'index'])->name('need-partner.index');
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
});
