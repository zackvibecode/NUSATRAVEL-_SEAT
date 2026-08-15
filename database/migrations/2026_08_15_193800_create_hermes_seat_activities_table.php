<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hermes_seat_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('departure_id')->nullable()->constrained()->nullOnDelete();
            $table->string('package_name');
            $table->date('departure_date');
            $table->integer('seat_delta');
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hermes_seat_activities');
    }
};
