<?php

use App\Http\Controllers\Api\HermesApiController;
use App\Http\Controllers\Api\ImportController;
use App\Http\Middleware\VerifyImportToken;
use Illuminate\Support\Facades\Route;

Route::middleware([VerifyImportToken::class, 'throttle:60,1'])->group(function () {
    Route::post('/imports/dropbox-excel', [ImportController::class, 'dropboxExcel'])
        ->name('api.imports.dropbox-excel');

    Route::get('/hermes/overview', [HermesApiController::class, 'overview']);
    Route::post('/hermes/chat', [HermesApiController::class, 'chat']);

    Route::get('/hermes/packages', [HermesApiController::class, 'packages']);
    Route::post('/hermes/packages', [HermesApiController::class, 'storePackage']);
    Route::get('/hermes/packages/{package}', [HermesApiController::class, 'showPackage']);
    Route::put('/hermes/packages/{package}', [HermesApiController::class, 'updatePackage']);
    Route::delete('/hermes/packages/{package}', [HermesApiController::class, 'destroyPackage']);

    Route::get('/hermes/departures', [HermesApiController::class, 'departures']);
    Route::post('/hermes/departures', [HermesApiController::class, 'storeDeparture']);
    Route::get('/hermes/departures/{departure}', [HermesApiController::class, 'showDeparture']);
    Route::put('/hermes/departures/{departure}', [HermesApiController::class, 'updateDeparture']);
    Route::delete('/hermes/departures/{departure}', [HermesApiController::class, 'destroyDeparture']);

    Route::get('/hermes/registrations', [HermesApiController::class, 'registrations']);
    Route::post('/hermes/registrations', [HermesApiController::class, 'storeRegistration']);
    Route::get('/hermes/registrations/{registration}', [HermesApiController::class, 'showRegistration']);
    Route::put('/hermes/registrations/{registration}', [HermesApiController::class, 'updateRegistration']);
    Route::delete('/hermes/registrations/{registration}', [HermesApiController::class, 'destroyRegistration']);
});
