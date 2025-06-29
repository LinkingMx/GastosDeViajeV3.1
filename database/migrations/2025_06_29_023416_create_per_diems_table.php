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
        Schema::create('per_diems', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('position_id')->comment('Referencia a positions');
            $table->unsignedBigInteger('detail_id')->comment('Referencia a expense_details');
            $table->enum('scope', ['domestic', 'foreign'])->comment('Alcance: nacional o extranjero');
            $table->string('currency', 3)->comment('Código ISO-4217 (MXN, USD, EUR)');
            $table->decimal('amount', 12, 2)->comment('Monto de viático diario');
            $table->date('valid_from')->comment('Fecha inicio de validez');
            $table->date('valid_to')->nullable()->comment('Fecha fin de validez');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('position_id')->references('id')->on('positions')->onDelete('cascade');
            $table->foreign('detail_id')->references('id')->on('expense_details')->onDelete('cascade');

            // Indexes
            $table->index('position_id');
            $table->index('detail_id');
            $table->index('scope');
            $table->index('currency');
            $table->index('valid_from');
            $table->index('valid_to');
            $table->index(['position_id', 'scope', 'currency']); // Composite index for common queries
            $table->index(['valid_from', 'valid_to']); // Date range queries

            // Unique constraint to prevent duplicate configurations
            $table->unique(['position_id', 'detail_id', 'scope', 'currency', 'valid_from'], 'unique_per_diem_config');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('per_diems');
    }
};
