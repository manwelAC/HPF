<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTblIncidentReportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_incident_report', function (Blueprint $table) {
        $table->id();
        $table->string('case_number', 25)->unique(); // IR-YYYYMMDD-XXXX
        $table->unsignedInteger('reported_by'); // FK tbl_employee.id
        $table->string('complainant_position', 155)->nullable();
        $table->datetime('report_datetime');
        $table->text('incident');
        $table->date('incident_date');
        $table->string('location', 255)->nullable();
        $table->text('witnesses')->nullable(); // free text for now
        $table->enum('status', ['pending', 'reviewed'])->default('pending');
        $table->datetime('date_created')->nullable();
        $table->integer('user_id_added');
        $table->timestamp('date_updated')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tbl_incident_report');
    }
}
