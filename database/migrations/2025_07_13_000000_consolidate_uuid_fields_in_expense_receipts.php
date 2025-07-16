<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('expense_receipts', function (Blueprint $table) {
            // Migrar datos de cfdi_uuid a uuid si uuid está vacío
            DB::statement('UPDATE expense_receipts SET uuid = cfdi_uuid WHERE uuid IS NULL AND cfdi_uuid IS NOT NULL');
            
            // Eliminar el campo cfdi_uuid duplicado
            $table->dropColumn('cfdi_uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expense_receipts', function (Blueprint $table) {
            // Restaurar el campo cfdi_uuid
            $table->string('cfdi_uuid', 36)->nullable()->after('photo_file_path')->comment('UUID del CFDI (campo legacy)');
            
            // Copiar datos de uuid a cfdi_uuid
            DB::statement('UPDATE expense_receipts SET cfdi_uuid = uuid WHERE cfdi_uuid IS NULL AND uuid IS NOT NULL');
        });
    }
};