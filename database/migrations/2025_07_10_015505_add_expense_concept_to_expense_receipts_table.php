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
        Schema::table('expense_receipts', function (Blueprint $table) {
            $table->foreignId('expense_concept_id')->nullable()->constrained()->onDelete('set null');
            $table->string('expense_category')->nullable()->comment('Categoría del gasto: lavanderia, alimentacion, transporte, etc.');
            $table->decimal('applied_amount', 10, 2)->nullable()->comment('Monto aplicado al concepto de gasto');
            
            $table->index(['expense_concept_id', 'expense_category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expense_receipts', function (Blueprint $table) {
            $table->dropForeign(['expense_concept_id']);
            $table->dropColumn(['expense_concept_id', 'expense_category', 'applied_amount']);
        });
    }
};
