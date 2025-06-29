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
        Schema::create('expense_concepts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Nombre oficial del concepto');
            $table->text('description')->nullable()->comment('Descripción detallada del concepto');
            $table->boolean('is_unmanaged')->default(false)->comment('Si permite detalles manuales');
            $table->timestamps();

            // Indexes
            $table->index('name');
            $table->index('is_unmanaged');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_concepts');
    }
};
