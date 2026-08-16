<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Nightly database backup at 02:00, cleanup old backups at 02:30 (Asia/Kuala_Lumpur).
Schedule::command('backup:clean')->daily()->at('02:30')->timezone('Asia/Kuala_Lumpur');
Schedule::command('backup:run')->daily()->at('02:00')->timezone('Asia/Kuala_Lumpur');
