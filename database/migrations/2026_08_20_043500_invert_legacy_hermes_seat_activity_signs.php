<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Older Hermes updates stored available-seat change (pax add = negative).
     * Flip existing rows so added people/capacity show as plus.
     */
    public function up(): void
    {
        DB::table('hermes_seat_activities')->update([
            'seat_delta' => DB::raw('-seat_delta'),
        ]);
    }

    public function down(): void
    {
        DB::table('hermes_seat_activities')->update([
            'seat_delta' => DB::raw('-seat_delta'),
        ]);
    }
};
