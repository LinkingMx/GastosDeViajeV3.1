<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Solo ejecutar si la columna attachment_type existe
        if (Schema::hasColumn('travel_request_attachments', 'attachment_type')) {
            // Paso 1: Crear un índice temporal para travel_request_id si no existe
            $this->createTemporaryIndex();

            // Paso 2: Eliminar el índice compuesto (si existe) y la columna enum
            $this->removeOldStructure();

            // Paso 3: Crear el nuevo índice compuesto
            $this->createNewIndex();

            // Paso 4: Limpiar el índice temporal
            $this->cleanupTemporaryIndex();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Solo ejecutar si la columna attachment_type no existe
        if (! Schema::hasColumn('travel_request_attachments', 'attachment_type')) {
            Schema::table('travel_request_attachments', function (Blueprint $table) {
                // Eliminar el índice del nuevo esquema si existe
                $this->dropIndexIfExists('tr_attachments_request_typeid_idx');

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
    }

    /**
     * Crear índice temporal para travel_request_id
     */
    private function createTemporaryIndex(): void
    {
        if (! $this->indexExists('tr_attachments_travel_request_temp_idx')) {
            Schema::table('travel_request_attachments', function (Blueprint $table) {
                $table->index('travel_request_id', 'tr_attachments_travel_request_temp_idx');
            });
        }
    }

    /**
     * Eliminar la estructura antigua
     */
    private function removeOldStructure(): void
    {
        Schema::table('travel_request_attachments', function (Blueprint $table) {
            // Eliminar el índice compuesto si existe
            $this->dropIndexIfExists('tr_attachments_request_type_idx');

            // Eliminar la columna enum
            if (Schema::hasColumn('travel_request_attachments', 'attachment_type')) {
                $table->dropColumn('attachment_type');
            }
        });
    }

    /**
     * Crear el nuevo índice compuesto
     */
    private function createNewIndex(): void
    {
        if (! $this->indexExists('tr_attachments_request_typeid_idx')) {
            Schema::table('travel_request_attachments', function (Blueprint $table) {
                $table->index(['travel_request_id', 'attachment_type_id'], 'tr_attachments_request_typeid_idx');
            });
        }
    }

    /**
     * Limpiar el índice temporal
     */
    private function cleanupTemporaryIndex(): void
    {
        if ($this->indexExists('tr_attachments_travel_request_temp_idx')) {
            Schema::table('travel_request_attachments', function (Blueprint $table) {
                $table->dropIndex('tr_attachments_travel_request_temp_idx');
            });
        }
    }

    /**
     * Verificar si un índice existe
     */
    private function indexExists(string $indexName): bool
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            $result = DB::select("
                SELECT COUNT(*) as count 
                FROM information_schema.statistics 
                WHERE table_schema = DATABASE() 
                AND table_name = 'travel_request_attachments' 
                AND index_name = ?
            ", [$indexName]);

            return $result[0]->count > 0;
        } elseif ($driver === 'sqlite') {
            $result = DB::select("
                SELECT COUNT(*) as count 
                FROM sqlite_master 
                WHERE type = 'index' 
                AND tbl_name = 'travel_request_attachments' 
                AND name = ?
            ", [$indexName]);

            return $result[0]->count > 0;
        }

        return false;
    }

    /**
     * Eliminar un índice si existe
     */
    private function dropIndexIfExists(string $indexName): void
    {
        if ($this->indexExists($indexName)) {
            Schema::table('travel_request_attachments', function (Blueprint $table) use ($indexName) {
                $table->dropIndex($indexName);
            });
        }
    }
};
