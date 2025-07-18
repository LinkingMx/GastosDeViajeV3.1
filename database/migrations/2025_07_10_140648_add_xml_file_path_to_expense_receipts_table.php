<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddXmlFilePathToExpenseReceiptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('expense_receipts', function (Blueprint $table) {
            if (!Schema::hasColumn('expense_receipts', 'xml_file_path')) {
                $table->string('xml_file_path')->nullable()->after('receipt_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('expense_receipts', function (Blueprint $table) {
            $table->dropColumn('xml_file_path');
        });
    }
}