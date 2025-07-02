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
            // Agregar timestamps para el workflow de autorización
            $table->timestamp('submitted_at')->nullable()->after('status');
            $table->timestamp('authorized_at')->nullable()->after('submitted_at');
            $table->timestamp('rejected_at')->nullable()->after('authorized_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('travel_requests', function (Blueprint $table) {
            // Eliminar los timestamps agregados
            $table->dropColumn(['submitted_at', 'authorized_at', 'rejected_at']);
        });
    }
};
