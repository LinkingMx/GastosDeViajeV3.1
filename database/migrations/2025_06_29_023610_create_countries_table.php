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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('iso2', 2)->unique()->comment('Código ISO-3166-1 alpha-2');
            $table->string('iso3', 3)->unique()->comment('Código ISO-3166-1 alpha-3');
            $table->string('name')->unique()->comment('Nombre oficial del país');
            $table->string('default_currency', 3)->nullable()->comment('Código ISO-4217');
            $table->boolean('is_foreign')->default(true)->comment('Si es considerado extranjero');
            $table->timestamps();

            // Indexes
            $table->index('iso2');
            $table->index('iso3');
            $table->index('name');
            $table->index('default_currency');
            $table->index('is_foreign');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
