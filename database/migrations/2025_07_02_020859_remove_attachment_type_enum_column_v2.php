<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class RemoveAttachmentTypeEnumColumnV2 extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $connection = Schema::connection(DB::connection());
        $driverName = DB::connection()->getDriverName();
        
        // Handle MySQL specific constraints
        if ($driverName === 'mysql') {
            // Get all foreign key constraints for the table
            $foreignKeys = collect(DB::select("
                SELECT CONSTRAINT_NAME, COLUMN_NAME
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'travel_request_attachments' 
                AND REFERENCED_TABLE_NAME IS NOT NULL
            "));
            
            // Store foreign key definitions for recreation
            $fkDefinitions = [];
            foreach ($foreignKeys as $fk) {
                $fkInfo = DB::select("
                    SELECT 
                        COLUMN_NAME,
                        REFERENCED_TABLE_NAME,
                        REFERENCED_COLUMN_NAME,
                        CONSTRAINT_NAME
                    FROM information_schema.KEY_COLUMN_USAGE
                    WHERE TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME = 'travel_request_attachments'
                    AND CONSTRAINT_NAME = ?
                ", [$fk->CONSTRAINT_NAME]);
                
                if (!empty($fkInfo)) {
                    $fkDefinitions[] = $fkInfo[0];
                }
            }
            
            // Drop foreign keys that might prevent index removal
            if ($foreignKeys->isNotEmpty()) {
                Schema::table('travel_request_attachments', function (Blueprint $table) use ($foreignKeys) {
                    foreach ($foreignKeys as $fk) {
                        try {
                            $table->dropForeign([$fk->COLUMN_NAME]);
                        } catch (\Exception $e) {
                            // Try with constraint name if column name doesn't work
                            try {
                                DB::statement("ALTER TABLE travel_request_attachments DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
                            } catch (\Exception $e2) {
                                // Foreign key might already be dropped
                            }
                        }
                    }
                });
            }
        }
        
        // Drop the index if it exists (works for both MySQL and SQLite)
        Schema::table('travel_request_attachments', function (Blueprint $table) use ($driverName) {
            try {
                if ($driverName === 'sqlite') {
                    // SQLite doesn't support dropIndex in the same way
                    DB::statement('DROP INDEX IF EXISTS tr_attachments_request_type_idx');
                } else {
                    // Check if index exists in MySQL
                    $indexExists = DB::select("
                        SELECT 1 
                        FROM information_schema.STATISTICS 
                        WHERE TABLE_SCHEMA = DATABASE() 
                        AND TABLE_NAME = 'travel_request_attachments' 
                        AND INDEX_NAME = 'tr_attachments_request_type_idx'
                    ");
                    
                    if (!empty($indexExists)) {
                        $table->dropIndex('tr_attachments_request_type_idx');
                    }
                }
            } catch (\Exception $e) {
                // Index might not exist
            }
        });
        
        // Remove the old attachment_type enum column if it exists
        Schema::table('travel_request_attachments', function (Blueprint $table) {
            if (Schema::hasColumn('travel_request_attachments', 'attachment_type')) {
                $table->dropColumn('attachment_type');
            }
        });
        
        // Recreate foreign keys for MySQL
        if ($driverName === 'mysql' && isset($fkDefinitions) && !empty($fkDefinitions)) {
            Schema::table('travel_request_attachments', function (Blueprint $table) use ($fkDefinitions) {
                foreach ($fkDefinitions as $fk) {
                    $table->foreign($fk->COLUMN_NAME)
                          ->references($fk->REFERENCED_COLUMN_NAME)
                          ->on($fk->REFERENCED_TABLE_NAME)
                          ->onDelete('cascade');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('travel_request_attachments', function (Blueprint $table) {
            // Recreate the enum column with a default value
            $table->enum('attachment_type', [
                'flight_reservation',
                'hotel_reservation',
                'advance_deposit_receipt',
                'other'
            ])->after('file_size')->default('other');
            
            // Recreate the index
            $table->index(['travel_request_id', 'attachment_type'], 'tr_attachments_request_type_idx');
        });
    }
}