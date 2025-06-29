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
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Nombre de la sucursal');
            $table->string('ceco')->unique()->comment('Código del centro de costo');
            $table->string('tax_id', 13)->nullable()->comment('RFC de identificación fiscal');
            $table->timestamps();

            // Indexes
            $table->index('name');
            $table->index('ceco');
            $table->index('tax_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
