<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmploymentStatusToTblEmployee extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('intra_payroll')->table('tbl_employee', function (Blueprint $table) {
            $table->string('employment_status')->nullable()->after('is_direct');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('intra_payroll')->table('tbl_employee', function (Blueprint $table) {
            $table->dropColumn('employment_status');
        });
    }
}
