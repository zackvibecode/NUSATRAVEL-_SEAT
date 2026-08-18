<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Invoice / payment / PIC fields synced from the source API.
     * Payment status is NOT stored — it is derived from invoice_status,
     * total_paid and balance on the Registration model.
     */
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->string('invoice_no', 255)->nullable()->index()->after('notes');
            $table->string('pic_utama', 255)->nullable()->after('invoice_no');
            $table->string('pic_in_house', 255)->nullable()->after('pic_utama');
            $table->string('invoice_status', 255)->nullable()->after('pic_in_house');
            $table->decimal('invoice_amount', 12, 2)->nullable()->after('invoice_status');
            $table->decimal('total_paid', 12, 2)->nullable()->after('invoice_amount');
            $table->string('invoice_url', 500)->nullable()->after('total_paid');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('pic_name', 255)->nullable()->index()->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropIndex(['invoice_no']);
            $table->dropColumn(['invoice_no', 'pic_utama', 'pic_in_house', 'invoice_status', 'invoice_amount', 'total_paid', 'invoice_url']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['pic_name']);
            $table->dropColumn('pic_name');
        });
    }
};
