<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // En SQLite es más fácil recrear la tabla con los nuevos tipos
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('DROP TABLE IF EXISTS travel_request_comments_backup');

            // Crear tabla temporal con la nueva estructura
            Schema::create('travel_request_comments_backup', function (Blueprint $table) {
                $table->id();
                $table->foreignId('travel_request_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained();
                $table->text('comment');
                $table->enum('type', ['submission', 'approval', 'rejection', 'revision', 'system', 'travel_approval', 'travel_rejection']);
                $table->timestamps();

                // Índices para mejorar performance
                $table->index(['travel_request_id', 'created_at']);
                $table->index('type');
            });

            // Copiar datos existentes
            DB::statement('INSERT INTO travel_request_comments_backup SELECT * FROM travel_request_comments');

            // Eliminar tabla original
            Schema::dropIfExists('travel_request_comments');

            // Renombrar tabla temporal
            DB::statement('ALTER TABLE travel_request_comments_backup RENAME TO travel_request_comments');
        } else {
            // Para otros DB como MySQL
            Schema::table('travel_request_comments', function (Blueprint $table) {
                $table->enum('type', ['submission', 'approval', 'rejection', 'revision', 'system', 'travel_approval', 'travel_rejection'])->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir los cambios eliminando los nuevos tipos
        Schema::table('travel_request_comments', function (Blueprint $table) {
            $table->enum('type', ['submission', 'approval', 'rejection', 'revision'])->change();
        });
    }
};
