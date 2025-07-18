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
        // No necesitamos hacer cambios en la estructura ya que status es un string
        // Esta migración es solo para documentar el nuevo estado 'pending_verification'
        
        // Actualizar cualquier solicitud travel_approved con depósito realizado al nuevo estado
        DB::table('travel_requests')
            ->where('status', 'travel_approved')
            ->where('advance_deposit_made', true)
            ->update(['status' => 'pending_verification']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir solicitudes en pending_verification a travel_approved
        DB::table('travel_requests')
            ->where('status', 'pending_verification')
            ->update(['status' => 'travel_approved']);
    }
};