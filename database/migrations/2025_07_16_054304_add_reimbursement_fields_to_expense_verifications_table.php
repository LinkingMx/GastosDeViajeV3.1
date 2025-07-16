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
            // Estado de reembolso: pending_reimbursement, reimbursed, closed
            $table->string('reimbursement_status')->nullable()->after('approval_notes');
            
            // Campos para el reembolso realizado por tesorería
            $table->boolean('reimbursement_made')->default(false)->after('reimbursement_status');
            $table->timestamp('reimbursement_made_at')->nullable()->after('reimbursement_made');
            $table->foreignId('reimbursement_made_by')->nullable()->constrained('users')->after('reimbursement_made_at');
            $table->decimal('reimbursement_amount', 10, 2)->nullable()->after('reimbursement_made_by');
            $table->text('reimbursement_notes')->nullable()->after('reimbursement_amount');
            
            // Archivos adjuntos del reembolso (similar a advance_deposit_attachment)
            $table->json('reimbursement_attachments')->nullable()->after('reimbursement_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expense_verifications', function (Blueprint $table) {
            $table->dropForeign(['reimbursement_made_by']);
            $table->dropColumn([
                'reimbursement_status',
                'reimbursement_made',
                'reimbursement_made_at',
                'reimbursement_made_by',
                'reimbursement_amount',
                'reimbursement_notes',
                'reimbursement_attachments'
            ]);
        });
    }
};
