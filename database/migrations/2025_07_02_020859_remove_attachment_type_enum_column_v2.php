<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveAttachmentTypeEnumColumnV2 extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('travel_request_attachments', function (Blueprint $table) {
            // First drop the index if it exists
            $table->dropIndex('tr_attachments_request_type_idx');
        });
        
        Schema::table('travel_request_attachments', function (Blueprint $table) {
            // Remove the old attachment_type enum column if it exists
            if (Schema::hasColumn('travel_request_attachments', 'attachment_type')) {
                $table->dropColumn('attachment_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('travel_request_attachments', function (Blueprint $table) {
            // Recreate the enum column
            $table->enum('attachment_type', [
                'flight_reservation',
                'hotel_reservation',
                'advance_deposit_receipt',
                'other'
            ])->after('file_size');
        });
    }
}