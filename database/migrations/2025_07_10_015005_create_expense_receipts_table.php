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
        Schema::create('expense_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_verification_id')->constrained()->onDelete('cascade');
            $table->enum('receipt_type', ['fiscal', 'non_deductible'])->default('non_deductible');
            $table->decimal('total_amount', 10, 2);
            $table->string('currency', 3)->default('MXN');
            $table->string('supplier_name');
            $table->string('supplier_rfc')->nullable();
            $table->date('receipt_date');
            $table->string('xml_file_path')->nullable();
            $table->string('pdf_file_path')->nullable();
            $table->string('photo_file_path')->nullable();
            $table->string('cfdi_uuid')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['expense_verification_id', 'receipt_type']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_receipts');
    }
};
