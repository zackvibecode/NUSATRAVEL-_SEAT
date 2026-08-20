<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hermes_seat_activities', function (Blueprint $table) {
            $table->string('activity_type')->nullable()->after('seat_delta');
            $table->string('actor_name')->nullable()->after('activity_type');
            $table->text('activity_note')->nullable()->after('actor_name');
            $table->index('activity_type');
        });
    }

    public function down(): void
    {
        Schema::table('hermes_seat_activities', function (Blueprint $table) {
            $table->dropIndex(['activity_type']);
            $table->dropColumn(['activity_type', 'actor_name', 'activity_note']);
        });
    }
};
