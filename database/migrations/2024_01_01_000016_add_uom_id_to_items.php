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
        Schema::table('items', function (Blueprint $table) {
            $table->foreignId('uom_id')->nullable()->after('hs_code')->constrained('uoms');
            
            // Keep the old unit_of_measure column for backward compatibility during migration
            // We'll remove it in a separate migration after data migration
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['uom_id']);
            $table->dropColumn('uom_id');
        });
    }
};