<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Capacity alert email
    |--------------------------------------------------------------------------
    | Daily digest of upcoming trips that are full or almost full.
    | Sent to every staff user's email address.
    */

    'alert_threshold' => (int) env('SEATWEB_ALERT_THRESHOLD', 5),

    'alert_days_ahead' => (int) env('SEATWEB_ALERT_DAYS_AHEAD', 30),

    /*
    |--------------------------------------------------------------------------
    | Backups (spatie/laravel-backup)
    |--------------------------------------------------------------------------
    | Where backup success/failure notifications are sent.
    */

    'backup_mail_to' => env('SEATWEB_BACKUP_MAIL_TO', env('MAIL_FROM_ADDRESS', 'hello@example.com')),

];
