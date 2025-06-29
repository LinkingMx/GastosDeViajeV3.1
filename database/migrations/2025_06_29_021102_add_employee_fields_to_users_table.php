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
        Schema::table('users', function (Blueprint $table) {
            // Foreign key references
            $table->unsignedBigInteger('position_id')->nullable()->after('remember_token');
            $table->unsignedBigInteger('department_id')->nullable()->after('position_id');
            $table->unsignedBigInteger('bank_id')->nullable()->after('department_id');

            // Employee financial information
            $table->string('clabe', 18)->nullable()->after('bank_id')->comment('CLABE interbancaria de 18 dígitos');
            $table->string('rfc', 13)->nullable()->after('clabe')->comment('RFC fiscal de 13 caracteres');
            $table->string('account_number')->nullable()->after('rfc')->comment('Número de cuenta bancaria');

            // Authorization overrides
            $table->boolean('override_authorization')->default(false)->after('account_number')->comment('Si anula el autorizador departamental');
            $table->unsignedBigInteger('override_authorizer_id')->nullable()->after('override_authorization')->comment('Autorizador personalizado');

            // Add foreign key constraints (uncomment when referenced tables are created)
            // $table->foreign('position_id')->references('id')->on('positions')->onDelete('set null');
            // $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
            // $table->foreign('bank_id')->references('id')->on('banks')->onDelete('set null');
            // $table->foreign('override_authorizer_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop foreign key constraints first (if they were added)
            // $table->dropForeign(['position_id']);
            // $table->dropForeign(['department_id']);
            // $table->dropForeign(['bank_id']);
            // $table->dropForeign(['override_authorizer_id']);

            // Drop columns in reverse order
            $table->dropColumn([
                'override_authorizer_id',
                'override_authorization',
                'account_number',
                'rfc',
                'clabe',
                'bank_id',
                'department_id',
                'position_id',
            ]);
        });
    }
};
