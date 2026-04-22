<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Normalization applied:
     * - employid is the sole employee reference
     * - All column names lowercased
     */
    public function up(): void
    {
        Schema::create('old_attachment_files', function (Blueprint $table) {
            $table->increments('id');
            $table->string('file_id', 100)->unique();
            $table->string('employid', 45)->nullable();
            $table->string('original_file_name', 150)->nullable();
            $table->string('file_location', 250)->nullable();
            $table->string('file_name', 250)->nullable();
            $table->string('file_type', 250)->nullable();
            $table->integer('file_size')->nullable();
            $table->dateTime('date_filed')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('old_attachment_files');
    }
};
