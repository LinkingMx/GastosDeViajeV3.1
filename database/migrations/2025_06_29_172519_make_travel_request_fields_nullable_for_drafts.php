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
        Schema::table('travel_requests', function (Blueprint $table) {
            // Make certain fields nullable to allow saving drafts with partial information
            $table->foreignId('origin_country_id')->nullable()->change();
            $table->string('origin_city')->nullable()->change();
            $table->foreignId('destination_country_id')->nullable()->change();
            $table->string('destination_city')->nullable()->change();
            $table->date('departure_date')->nullable()->change();
            $table->date('return_date')->nullable()->change();
            $table->enum('request_type', ['domestic', 'foreign'])->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('travel_requests', function (Blueprint $table) {
            // Revert back to non-nullable
            $table->foreignId('origin_country_id')->nullable(false)->change();
            $table->string('origin_city')->nullable(false)->change();
            $table->foreignId('destination_country_id')->nullable(false)->change();
            $table->string('destination_city')->nullable(false)->change();
            $table->date('departure_date')->nullable(false)->change();
            $table->date('return_date')->nullable(false)->change();
            $table->enum('request_type', ['domestic', 'foreign'])->nullable(false)->change();
        });
    }
};
