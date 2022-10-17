<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('audio', function (Blueprint $table) {
            $table->id();
            $table->string('owner', 30);
            $table->integer('status')->default(1);
            $table->unsignedBigInteger('id_landing_page');
            $table->foreign('id_landing_page')->references('id')->on('landing_pages')->onDelete('cascade');
            $table->unsignedBigInteger('id_file');
            $table->foreign('id_file')->references('id')->on('files');

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
        Schema::dropIfExists('audio');
    }
};