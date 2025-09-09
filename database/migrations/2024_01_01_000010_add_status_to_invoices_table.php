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
            $table->enum('status', ['draft', 'active', 'discarded'])->default('draft')->after('invoice_type');
            $table->text('discard_reason')->nullable()->after('fbr_error_message');
            $table->timestamp('discarded_at')->nullable()->after('discard_reason');
            $table->foreignId('discarded_by')->nullable()->constrained('users')->after('discarded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['discarded_by']);
            $table->dropColumn(['status', 'discard_reason', 'discarded_at', 'discarded_by']);
        });
    }
};