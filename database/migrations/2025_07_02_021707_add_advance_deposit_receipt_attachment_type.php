<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Agregar el nuevo tipo de documento para comprobantes de depósito
        DB::table('attachment_types')->insert([
            'name' => 'Comprobante de Depósito',
            'slug' => 'advance_deposit_receipt',
            'description' => 'Comprobante del depósito de anticipo realizado por tesorería',
            'icon' => 'heroicon-o-banknotes',
            'color' => 'emerald',
            'sort_order' => 0, // Orden más alto para que aparezca primero
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Actualizar el sort_order de los tipos existentes para que este nuevo aparezca primero
        DB::table('attachment_types')
            ->where('slug', '!=', 'advance_deposit_receipt')
            ->increment('sort_order');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar el tipo de documento de comprobante de depósito
        DB::table('attachment_types')
            ->where('slug', 'advance_deposit_receipt')
            ->delete();

        // Restaurar el sort_order de los tipos existentes
        DB::table('attachment_types')
            ->where('slug', '!=', 'advance_deposit_receipt')
            ->decrement('sort_order');
    }
};
