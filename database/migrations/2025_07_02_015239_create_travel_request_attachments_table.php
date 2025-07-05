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
        Schema::create('travel_request_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->string('file_name'); // Nombre original del archivo
            $table->string('file_path'); // Ruta donde se almacena el archivo
            $table->string('file_type'); // Tipo de archivo (mime type)
            $table->integer('file_size'); // Tamaño en bytes
            $table->enum('attachment_type', [
                'hotel_reservation',
                'flight_ticket',
                'transport_receipt',
                'other_document',
            ])->default('other_document'); // Tipo de documento
            $table->text('description')->nullable(); // Descripción opcional del documento
            $table->timestamps();

            // Índices para mejorar el rendimiento
            $table->index(['travel_request_id', 'attachment_type'], 'tr_attachments_request_type_idx');
            $table->index('uploaded_by', 'tr_attachments_uploader_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_request_attachments');
    }
};
