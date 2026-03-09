<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTblNteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_nte', function (Blueprint $table) {
        $table->id();
        $table->string('case_number', 25)->unique(); // NTE-YYYYMMDD-XXXX
        $table->unsignedInteger('ir_id'); // FK tbl_incident_report.id
        $table->unsignedInteger('employee_id'); // FK tbl_employee.id
        $table->text('case_details');
        $table->text('remarks')->nullable();
        $table->date('date_served')->nullable();
        $table->date('due_date')->nullable();
        $table->text('resolution')->nullable();
        $table->text('employee_reply')->nullable();
        $table->datetime('reply_date')->nullable();
        $table->enum('status', ['pending', 'replied', 'closed'])->default('pending');
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
        Schema::dropIfExists('tbl_nte');
    }
}
