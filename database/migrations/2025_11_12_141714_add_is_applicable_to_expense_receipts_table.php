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
        Schema::table('expense_receipts', function (Blueprint $table) {
            $table->boolean('is_applicable')
                ->default(true)
                ->after('applied_amount')
                ->comment('Indica si el comprobante es aplicable o fue marcado como no aplicable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expense_receipts', function (Blueprint $table) {
            $table->dropColumn('is_applicable');
        });
    }
};
