<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTblDisciplinaryActionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_disciplinary_action', function (Blueprint $table) {
        $table->id();
        $table->string('case_number', 25)->unique(); // DA-YYYYMMDD-XXXX
        $table->unsignedInteger('nte_id'); // FK tbl_nte.id
        $table->unsignedInteger('employee_id'); // FK tbl_employee.id
        $table->text('case_details');
        $table->text('remarks')->nullable();
        $table->enum('sanction', [
            'written_warning',
            'suspension',
            'demotion',
            'termination',
            'reprimand',
            'others'
        ]);
        $table->text('sanction_details')->nullable(); // e.g. suspension duration, others description
        $table->datetime('date_issued')->nullable();
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
        Schema::dropIfExists('tbl_disciplinary_action');
    }
}
