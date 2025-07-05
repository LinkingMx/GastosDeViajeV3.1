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
            // Agregar columnas necesarias para el equipo de viajes
            if (! Schema::hasColumn('travel_requests', 'travel_reviewed_by')) {
                $table->foreignId('travel_reviewed_by')->nullable()->constrained('users');
            }
            if (! Schema::hasColumn('travel_requests', 'travel_review_comments')) {
                $table->text('travel_review_comments')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('travel_requests', function (Blueprint $table) {
            // Eliminar las columnas agregadas
            if (Schema::hasColumn('travel_requests', 'travel_reviewed_by')) {
                $table->dropForeign(['travel_reviewed_by']);
                $table->dropColumn('travel_reviewed_by');
            }
            if (Schema::hasColumn('travel_requests', 'travel_review_comments')) {
                $table->dropColumn('travel_review_comments');
            }
        });
    }
};
