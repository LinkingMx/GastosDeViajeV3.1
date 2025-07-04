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
        Schema::table('positions', function (Blueprint $table) {
            // Check if the level column doesn't already exist
            if (! Schema::hasColumn('positions', 'level')) {
                $table->integer('level')->default(1)->after('description')->comment('Nivel jerárquico del puesto (1=básico, 2=medio, 3=senior, etc.)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            if (Schema::hasColumn('positions', 'level')) {
                $table->dropColumn('level');
            }
        });
    }
};
