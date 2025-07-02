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
        Schema::create('attachment_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Hotel Reservation, Flight Ticket, etc.
            $table->string('slug')->unique(); // hotel_reservation, flight_ticket, etc.
            $table->text('description')->nullable(); // Descripción del tipo de documento
            $table->string('icon')->nullable(); // Ícono para mostrar en la UI
            $table->string('color')->default('gray'); // Color del badge/ícono
            $table->boolean('is_active')->default(true); // Si está activo o no
            $table->integer('sort_order')->default(0); // Orden de aparición
            $table->timestamps();

            // Índices
            $table->index(['is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachment_types');
    }
};
