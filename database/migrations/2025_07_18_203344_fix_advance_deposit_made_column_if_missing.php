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
        if (!Schema::hasColumn('travel_requests', 'advance_deposit_made')) {
            Schema::table('travel_requests', function (Blueprint $table) {
                $table->boolean('advance_deposit_made')->default(false)->after('rejected_at');
            });
        }
        
        if (!Schema::hasColumn('travel_requests', 'advance_deposit_made_at')) {
            Schema::table('travel_requests', function (Blueprint $table) {
                $table->timestamp('advance_deposit_made_at')->nullable()->after('advance_deposit_made');
            });
        }
        
        if (!Schema::hasColumn('travel_requests', 'advance_deposit_made_by')) {
            Schema::table('travel_requests', function (Blueprint $table) {
                $table->foreignId('advance_deposit_made_by')->nullable()->constrained('users')->after('advance_deposit_made_at');
            });
        }
        
        if (!Schema::hasColumn('travel_requests', 'advance_deposit_notes')) {
            Schema::table('travel_requests', function (Blueprint $table) {
                $table->text('advance_deposit_notes')->nullable()->after('advance_deposit_made_by');
            });
        }
        
        if (!Schema::hasColumn('travel_requests', 'advance_deposit_amount')) {
            Schema::table('travel_requests', function (Blueprint $table) {
                $table->decimal('advance_deposit_amount', 10, 2)->nullable()->after('advance_deposit_notes');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('travel_requests', function (Blueprint $table) {
            if (Schema::hasColumn('travel_requests', 'advance_deposit_made_by')) {
                $table->dropForeign(['advance_deposit_made_by']);
            }
        });
        
        $columnsToRemove = [
            'advance_deposit_made',
            'advance_deposit_made_at',
            'advance_deposit_made_by',
            'advance_deposit_notes',
            'advance_deposit_amount'
        ];
        
        foreach ($columnsToRemove as $column) {
            if (Schema::hasColumn('travel_requests', $column)) {
                Schema::table('travel_requests', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};