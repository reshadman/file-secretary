<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system__files', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uuid')->index();
            $table->string('context', 64)->index();
            $table->string('original_name')->nullable();
            $table->string('file_name');
            $table->string('extension', 12);
            $table->string('sibling_folder')->nullable();
            $table->string('context_folder')->nullable();
            $table->string('file_hash')->nullable();
            $table->string('file_ensured_hash')->nullable();
            $table->integer('file_size')->nullable();
            $table->string('category', 20);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('system__files');
    }
}
