<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Normalization applied:
     * - Removed empname, empposition, empclass, date_hired → derivable via empid
     * - approver1, approver2 renamed to approver1_emp_num, approver2_emp_num
     *   (approver name/position/dept derivable via those emp_num references)
     */
    public function up(): void
    {
        Schema::create('old_coe_record', function (Blueprint $table) {
            $table->increments('id');
            $table->string('empid', 45)->nullable();
            $table->string('purpose', 100)->nullable();
            $table->dateTime('date_request')->nullable();
            $table->string('coe_type', 100)->nullable();
            $table->string('approver1_emp_num', 45)->nullable();
            $table->string('approver2_emp_num', 45)->nullable();
            $table->string('status', 100)->nullable();
            $table->longText('remarks')->nullable();
            $table->string('pcn_status', 45)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('old_coe_record');
    }
};
