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
        Schema::create('expense_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('concept_id')->comment('Referencia a expense_concepts');
            $table->string('name')->unique()->comment('Nombre único del detalle');
            $table->text('description')->nullable()->comment('Descripción opcional del detalle');
            $table->boolean('is_active')->default(true)->comment('Si está actualmente habilitado');
            $table->integer('priority')->default(0)->comment('Prioridad para ordenamiento, mayor valor = mayor prioridad');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('concept_id')->references('id')->on('expense_concepts')->onDelete('cascade');

            // Indexes
            $table->index('concept_id');
            $table->index('name');
            $table->index('is_active');
            $table->index(['concept_id', 'is_active']); // Composite index for filtered queries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_details');
    }
};
