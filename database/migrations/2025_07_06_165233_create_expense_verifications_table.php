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
        Schema::create('expense_verifications', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->comment('Folio UUID autogenerado para la comprobación');
            $table->unsignedBigInteger('travel_request_id')->comment('ID de la solicitud de viaje asociada');
            $table->unsignedBigInteger('created_by')->comment('ID del usuario que crea la comprobación');
            $table->timestamps();

            // Foreign keys
            $table->foreign('travel_request_id')->references('id')->on('travel_requests')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');

            // Indexes
            $table->index(['travel_request_id', 'created_by']);
            $table->index('uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_verifications');
    }
};
