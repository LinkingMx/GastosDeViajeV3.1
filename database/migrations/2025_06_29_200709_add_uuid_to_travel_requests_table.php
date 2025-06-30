<?php

use App\Models\TravelRequest;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Verificar si la columna uuid ya existe
        if (! Schema::hasColumn('travel_requests', 'uuid')) {
            // Agregar el campo UUID como nullable
            Schema::table('travel_requests', function (Blueprint $table) {
                $table->uuid('uuid')->nullable()->unique()->after('id');
            });
        }

        // Generar UUIDs para registros existentes que no tengan uno
        TravelRequest::whereNull('uuid')->chunk(100, function ($requests) {
            foreach ($requests as $request) {
                $request->update(['uuid' => Str::uuid()]);
            }
        });

        // No intentamos cambiar el campo a NOT NULL en SQLite por limitaciones
        // El modelo manejará la generación automática de UUIDs para nuevos registros
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('travel_requests', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
