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
        Schema::table('general_settings', function (Blueprint $table) {
            $table->foreignId('autorizador_mayor_id')
                ->nullable()
                ->after('dias_minimos_anticipacion')
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Usuario asignado como Autorizador Mayor para montos superiores al límite');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->dropForeign(['autorizador_mayor_id']);
            $table->dropColumn('autorizador_mayor_id');
        });
    }
};
