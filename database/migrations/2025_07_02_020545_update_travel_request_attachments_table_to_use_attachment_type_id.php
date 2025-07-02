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
            // Agregar la nueva columna de relación
            $table->foreignId('attachment_type_id')->nullable()->after('file_size')->constrained('attachment_types')->onDelete('restrict');

            // Índice para mejorar el rendimiento
            $table->index('attachment_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('travel_request_attachments', function (Blueprint $table) {
            $table->dropForeign(['attachment_type_id']);
            $table->dropIndex(['attachment_type_id']);
            $table->dropColumn('attachment_type_id');
        });
    }
};
