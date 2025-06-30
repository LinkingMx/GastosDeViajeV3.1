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
        Schema::create('travel_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('authorizer_id')->nullable()->constrained('users');
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('origin_country_id')->constrained('countries');
            $table->string('origin_city');
            $table->foreignId('destination_country_id')->constrained('countries');
            $table->string('destination_city');
            $table->date('departure_date');
            $table->date('return_date');
            $table->string('status')->default('draft');
            $table->enum('request_type', ['domestic', 'foreign']);
            $table->text('notes')->nullable();
            $table->json('additional_services')->nullable();
            $table->json('per_diem_data')->nullable();
            $table->json('custom_expenses_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_requests');
    }
};
