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
        // Para SQLite, necesitamos recrear la tabla con el nuevo enum
        if (DB::getDriverName() === 'sqlite') {
            // Renombrar tabla actual
            Schema::rename('travel_request_comments', 'travel_request_comments_old');
            
            // Crear nueva tabla con el enum actualizado
            Schema::create('travel_request_comments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('travel_request_id')->constrained();
                $table->foreignId('user_id')->nullable()->constrained();
                $table->text('comment');
                $table->enum('type', ['submission', 'approval', 'rejection', 'revision', 'system', 'travel_approval', 'travel_rejection', 'travel_edit_approval', 'treasury_deposit', 'treasury_unmark']);
                $table->timestamps();

                // Índices para mejorar performance
                $table->index(['travel_request_id', 'created_at']);
                $table->index('type');
            });
            
            // Copiar datos de la tabla antigua
            DB::statement('INSERT INTO travel_request_comments (id, travel_request_id, user_id, comment, type, created_at, updated_at) 
                          SELECT id, travel_request_id, user_id, comment, type, created_at, updated_at 
                          FROM travel_request_comments_old');
            
            // Eliminar tabla antigua
            Schema::drop('travel_request_comments_old');
        } else {
            // Para otros DB como MySQL
            Schema::table('travel_request_comments', function (Blueprint $table) {
                $table->enum('type', ['submission', 'approval', 'rejection', 'revision', 'system', 'travel_approval', 'travel_rejection', 'travel_edit_approval', 'treasury_deposit', 'treasury_unmark'])->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Para SQLite, necesitamos recrear la tabla sin los nuevos tipos
        if (DB::getDriverName() === 'sqlite') {
            // Renombrar tabla actual
            Schema::rename('travel_request_comments', 'travel_request_comments_new');
            
            // Crear tabla con el enum anterior
            Schema::create('travel_request_comments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('travel_request_id')->constrained();
                $table->foreignId('user_id')->nullable()->constrained();
                $table->text('comment');
                $table->enum('type', ['submission', 'approval', 'rejection', 'revision', 'system', 'travel_approval', 'travel_rejection', 'travel_edit_approval']);
                $table->timestamps();

                // Índices para mejorar performance
                $table->index(['travel_request_id', 'created_at']);
                $table->index('type');
            });
            
            // Copiar datos excluyendo los nuevos tipos
            DB::statement('INSERT INTO travel_request_comments (id, travel_request_id, user_id, comment, type, created_at, updated_at) 
                          SELECT id, travel_request_id, user_id, comment, type, created_at, updated_at 
                          FROM travel_request_comments_new 
                          WHERE type NOT IN ("treasury_deposit", "treasury_unmark")');
            
            // Eliminar tabla nueva
            Schema::drop('travel_request_comments_new');
        } else {
            // Para otros DB como MySQL
            Schema::table('travel_request_comments', function (Blueprint $table) {
                $table->enum('type', ['submission', 'approval', 'rejection', 'revision', 'system', 'travel_approval', 'travel_rejection', 'travel_edit_approval'])->change();
            });
        }
    }
};