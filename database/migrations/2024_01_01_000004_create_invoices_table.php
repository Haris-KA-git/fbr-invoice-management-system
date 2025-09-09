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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_profile_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained();
            $table->string('invoice_number');
            $table->string('fbr_invoice_number')->nullable();
            $table->date('invoice_date');
            $table->enum('invoice_type', ['sales', 'purchase', 'debit_note', 'credit_note'])->default('sales');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('sales_tax', 15, 2)->default(0);
            $table->decimal('fed_amount', 15, 2)->default(0);
            $table->decimal('further_tax', 15, 2)->default(0);
            $table->decimal('withheld_tax', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->json('fbr_json_data')->nullable();
            $table->enum('fbr_status', ['pending', 'validated', 'submitted', 'failed'])->default('pending');
            $table->text('fbr_response')->nullable();
            $table->text('fbr_error_message')->nullable();
            $table->string('qr_code')->nullable();
            $table->timestamps();
            
            $table->index(['business_profile_id', 'invoice_date']);
            $table->index(['fbr_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};