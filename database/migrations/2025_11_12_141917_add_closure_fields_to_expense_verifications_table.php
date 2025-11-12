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
        Schema::table('expense_verifications', function (Blueprint $table) {
            // Closure tracking - for final closure state
            $table->timestamp('closed_at')->nullable()->after('archived_by');
            $table->text('closure_notes')->nullable()->after('closed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expense_verifications', function (Blueprint $table) {
            $table->dropColumn(['closed_at', 'closure_notes']);
        });
    }
};
