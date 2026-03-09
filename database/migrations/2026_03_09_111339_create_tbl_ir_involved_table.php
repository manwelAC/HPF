<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTblIrInvolvedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_ir_involved', function (Blueprint $table) {
        $table->id();
        $table->unsignedInteger('ir_id'); // FK tbl_incident_report.id
        $table->unsignedInteger('employee_id'); // FK tbl_employee.id
        $table->timestamp('date_updated')->useCurrent()->useCurrentOnUpdate();
        $table->datetime('date_created')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_ir_involved');
    }
}
