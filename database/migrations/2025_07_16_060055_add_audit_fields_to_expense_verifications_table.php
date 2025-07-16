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
            // Campos para reapertura y auditoría
            $table->boolean('is_reopened')->default(false)->after('reimbursement_attachments');
            $table->timestamp('reopened_at')->nullable()->after('is_reopened');
            $table->foreignId('reopened_by')->nullable()->constrained('users')->after('reopened_at');
            $table->text('reopening_reason')->nullable()->after('reopened_by');
            
            // Notas administrativas para históricos
            $table->text('administrative_notes')->nullable()->after('reopening_reason');
            
            // Log de cambios en JSON
            $table->json('audit_log')->nullable()->after('administrative_notes');
            
            // Campo para identificar si está archivada
            $table->boolean('is_archived')->default(false)->after('audit_log');
            $table->timestamp('archived_at')->nullable()->after('is_archived');
            $table->foreignId('archived_by')->nullable()->constrained('users')->after('archived_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expense_verifications', function (Blueprint $table) {
            $table->dropForeign(['reopened_by']);
            $table->dropForeign(['archived_by']);
            $table->dropColumn([
                'is_reopened',
                'reopened_at',
                'reopened_by',
                'reopening_reason',
                'administrative_notes',
                'audit_log',
                'is_archived',
                'archived_at',
                'archived_by'
            ]);
        });
    }
};
