<?php

use App\Http\Controllers\Api\ImportController;
use App\Http\Middleware\VerifyImportToken;
use Illuminate\Support\Facades\Route;

Route::middleware(VerifyImportToken::class)->group(function () {
    Route::post('/imports/dropbox-excel', [ImportController::class, 'dropboxExcel'])
        ->name('api.imports.dropbox-excel');
});
