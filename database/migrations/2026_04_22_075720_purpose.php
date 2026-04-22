<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Normalization applied:
     * - Removed created_by_emp_name, created_by_emp_dept → derivable via created_by_emp_num
     * - Removed updated_by_emp_name, updated_by_emp_dept → derivable via updated_by_emp_num
     * - Fixed column types: all were `text`, now string(45) for emp_num, dateTime for dates
     * - Empty strings in source data handled via NULLIF in migration SQL
     */
    public function up(): void
    {
        Schema::create('old_purpose', function (Blueprint $table) {
            $table->increments('id');
            $table->text('purpose')->nullable();
            $table->string('created_by_emp_num', 45)->nullable();
            $table->dateTime('date_created')->nullable();
            $table->string('updated_by_emp_num', 45)->nullable();
            $table->dateTime('date_updated')->nullable();
        });
    }
};
