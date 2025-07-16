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
        Schema::table('travel_requests', function (Blueprint $table) {
            $table->boolean('advance_deposit_made')->default(false)->after('rejected_at');
            $table->timestamp('advance_deposit_made_at')->nullable()->after('advance_deposit_made');
            $table->foreignId('advance_deposit_made_by')->nullable()->constrained('users')->after('advance_deposit_made_at');
            $table->text('advance_deposit_notes')->nullable()->after('advance_deposit_made_by');
            $table->decimal('advance_deposit_amount', 10, 2)->nullable()->after('advance_deposit_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('travel_requests', function (Blueprint $table) {
            $table->dropForeign(['advance_deposit_made_by']);
            $table->dropColumn([
                'advance_deposit_made',
                'advance_deposit_made_at',
                'advance_deposit_made_by',
                'advance_deposit_notes',
                'advance_deposit_amount'
            ]);
        });
    }
};