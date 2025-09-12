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
            // Make hs_code required and add index
            $table->string('hs_code')->nullable(false)->change();
            $table->index('hs_code');
            
            // Add unique constraint for hs_code + business_profile_id
            $table->unique(['business_profile_id', 'hs_code'], 'unique_hs_code_per_business');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropUnique('unique_hs_code_per_business');
            $table->dropIndex(['hs_code']);
            $table->string('hs_code')->nullable()->change();
        });
    }
};