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
        Schema::table('travel_request_attachments', function (Blueprint $table) {
            // Primero eliminar el índice que incluye la columna attachment_type
            try {
                $table->dropIndex('tr_attachments_request_type_idx');
            } catch (\Exception $e) {
                // El índice puede no existir en algunos casos
            }

            // Luego eliminar la columna enum ya que ahora usamos attachment_type_id
            $table->dropColumn('attachment_type');

            // Crear un nuevo índice compuesto para travel_request_id y attachment_type_id
            $table->index(['travel_request_id', 'attachment_type_id'], 'tr_attachments_request_typeid_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('travel_request_attachments', function (Blueprint $table) {
            // Eliminar el índice del nuevo esquema
            $table->dropIndex('tr_attachments_request_typeid_idx');

            // Restaurar la columna enum
            $table->enum('attachment_type', [
                'hotel_reservation',
                'flight_ticket',
                'transport_receipt',
                'other_document',
            ])->default('other_document')->after('file_size');

            // Restaurar el índice con el nombre personalizado
            $table->index(['travel_request_id', 'attachment_type'], 'tr_attachments_request_type_idx');
        });
    }
};
