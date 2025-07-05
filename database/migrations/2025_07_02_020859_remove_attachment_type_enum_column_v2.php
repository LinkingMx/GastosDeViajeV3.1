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
            // Primero eliminar los índices que usan la columna attachment_type (usando el nombre personalizado)
            $table->dropIndex('tr_attachments_request_type_idx');

            // Luego eliminar la columna enum ya que ahora usamos attachment_type_id
            $table->dropColumn('attachment_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('travel_request_attachments', function (Blueprint $table) {
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
