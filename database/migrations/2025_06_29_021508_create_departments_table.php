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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 128)->unique()->comment('Nombre del departamento');
            $table->text('description')->nullable()->comment('Descripción opcional del departamento');
            $table->unsignedBigInteger('authorizer_id')->nullable()->comment('Autorizador por defecto del departamento');
            $table->timestamps();

            // Foreign key constraint (self-referencing to users table)
            $table->foreign('authorizer_id')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index('authorizer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
