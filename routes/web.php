<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartureController;
use App\Http\Controllers\HermesGuideController;
use App\Http\Controllers\NeedPartnerController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

// Public login / logout (PRD section 4.2)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Password reset (public)
Route::get('/forgot-password', [PasswordResetController::class, 'requestForm'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->middleware('throttle:5,1')->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'resetForm'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:5,1')->name('password.update');

// All internal pages require authentication (PRD section 4.2)
Route::middleware('auth')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('hermes/updates', [HermesGuideController::class, 'updates'])->name('hermes.updates');

    // Sales can view trips and register customers
    Route::get('departures', [DepartureController::class, 'index'])->name('departures.index');
    Route::get('departures/{departure}', [DepartureController::class, 'show'])
        ->whereNumber('departure')->name('departures.show');
    Route::get('departures/{departure}/manifest', [DepartureController::class, 'manifest'])
        ->whereNumber('departure')->name('departures.manifest');

    Route::post('registrations', [RegistrationController::class, 'store'])->name('registrations.store');
    Route::put('registrations/{registration}', [RegistrationController::class, 'update'])->name('registrations.update');
    Route::delete('registrations/{registration}', [RegistrationController::class, 'destroy'])->name('registrations.destroy');

    Route::get('participants', [ParticipantController::class, 'index'])->name('participants.index');
    Route::get('need-partner', [NeedPartnerController::class, 'index'])->name('need-partner.index');
    Route::get('calendar', [CalendarController::class, 'index'])->name('calendar.index');

    // Admin-only area
    Route::middleware('admin')->group(function () {
        Route::get('dashboard/attention-trips', [DashboardController::class, 'attentionTrips'])->name('dashboard.attention-trips');
        Route::get('hermes', [HermesGuideController::class, 'index'])->name('hermes.guide');
        Route::get('hermes/chat', [HermesGuideController::class, 'chat'])->name('hermes.chat');
        Route::post('hermes/chat', [HermesGuideController::class, 'chatMessage'])->middleware('throttle:30,1')->name('hermes.chat.message');

        Route::resource('packages', PackageController::class)->except(['show']);

        Route::get('departures/create', [DepartureController::class, 'create'])->name('departures.create');
        Route::post('departures', [DepartureController::class, 'store'])->name('departures.store');
        Route::get('departures/{departure}/edit', [DepartureController::class, 'edit'])->name('departures.edit');
        Route::put('departures/{departure}', [DepartureController::class, 'update'])->name('departures.update');

        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');

        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});
