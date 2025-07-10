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
            // Eliminar la foreign key incorrecta y renombrar la columna
            $table->dropForeign(['expense_concept_id']);
            $table->renameColumn('expense_concept_id', 'expense_detail_id');
            
            // Agregar la nueva foreign key a expense_details
            $table->foreign('expense_detail_id')->references('id')->on('expense_details')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expense_receipts', function (Blueprint $table) {
            // Revertir los cambios
            $table->dropForeign(['expense_detail_id']);
            $table->renameColumn('expense_detail_id', 'expense_concept_id');
            
            // Restaurar la foreign key original
            $table->foreign('expense_concept_id')->references('id')->on('expense_concepts')->onDelete('set null');
        });
    }
};
