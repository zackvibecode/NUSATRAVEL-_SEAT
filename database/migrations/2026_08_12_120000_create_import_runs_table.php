<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_runs', function (Blueprint $table) {
            $table->id();
            $table->string('source')->default('dropbox-excel');
            $table->string('filename')->nullable();
            $table->string('dropbox_path')->nullable();
            $table->boolean('dry_run')->default(false);
            $table->string('status')->default('completed'); // completed | failed | dry_run
            $table->unsignedInteger('packages_created')->default(0);
            $table->unsignedInteger('packages_updated')->default(0);
            $table->unsignedInteger('departures_created')->default(0);
            $table->unsignedInteger('departures_updated')->default(0);
            $table->unsignedInteger('registrations_created')->default(0);
            $table->unsignedInteger('registrations_updated')->default(0);
            $table->unsignedInteger('skipped')->default(0);
            $table->json('errors')->nullable();
            $table->json('summary')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_runs');
    }
};
