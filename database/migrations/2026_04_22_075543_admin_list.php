<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Normalization applied:
     * - Removed admin_name, admin_position, admin_dept     → derivable via admin_id
     * - Removed created_by_emp_name, created_by_emp_dept   → derivable via created_by_emp_num
     * - Removed updated_by_emp_name, updated_by_emp_dept   → derivable via updated_by_emp_num
     * - Removed admin_listcol                              → no business meaning retained
     */
    public function up(): void
    {
        Schema::create('old_admin_list', function (Blueprint $table) {
            $table->increments('id');
            $table->string('admin_id', 45)->unique();
            $table->string('created_by_emp_num', 45)->nullable();
            $table->dateTime('date_created')->nullable();
            $table->string('updated_by_emp_num', 45)->nullable();
            $table->dateTime('date_updated')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('old_admin_list');
    }
};
