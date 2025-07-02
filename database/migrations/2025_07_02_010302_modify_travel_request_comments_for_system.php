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
        Schema::table('travel_request_comments', function (Blueprint $table) {
            // Hacer que user_id sea nullable para comentarios del sistema
            $table->foreignId('user_id')->nullable()->change();

            // Agregar tipos para el equipo de viajes al enum
            $table->enum('type', ['submission', 'approval', 'rejection', 'revision', 'system', 'travel_approval', 'travel_rejection'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('travel_request_comments', function (Blueprint $table) {
            // Revertir los cambios
            $table->foreignId('user_id')->nullable(false)->change();
            $table->enum('type', ['submission', 'approval', 'rejection', 'revision'])->change();
        });
    }
};
