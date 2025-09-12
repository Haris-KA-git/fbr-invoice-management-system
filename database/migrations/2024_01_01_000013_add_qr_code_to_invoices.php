<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('qr_code_path')->nullable()->after('qr_code');
            $table->string('fbr_verification_url')->nullable()->after('qr_code_path');
            $table->string('usin')->nullable()->after('fbr_invoice_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['qr_code_path', 'fbr_verification_url', 'usin']);
        });
    }
};