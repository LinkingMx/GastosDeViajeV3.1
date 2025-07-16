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
            // Agregar campos específicos para comprobantes fiscales
            $table->string('uuid', 36)->nullable()->after('cfdi_uuid')->comment('UUID fiscal del CFDI');
            $table->text('concept')->nullable()->after('uuid')->comment('Concepto o descripción del comprobante fiscal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expense_receipts', function (Blueprint $table) {
            $table->dropColumn(['uuid', 'concept']);
        });
    }
};
