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
        Schema::table('travel_requests', function (Blueprint $table) {
            // Solo agregar la columna que falta
            $table->text('travel_review_comments')->nullable();

            // Agregar foreign key para travel_reviewed_by si no existe
            try {
                $table->foreign('travel_reviewed_by')->references('id')->on('users');
            } catch (\Exception $e) {
                // Ya existe la foreign key
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('travel_requests', function (Blueprint $table) {
            try {
                $table->dropForeign(['travel_reviewed_by']);
            } catch (\Exception $e) {
                // Foreign key no existe
            }
            $table->dropColumn(['travel_review_comments']);
        });
    }
};
