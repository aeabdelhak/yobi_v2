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
        Schema::create('has_offers', function (Blueprint $table) {
            $table->id();
            $table->integer('status')->default(1);
            $table->unsignedBigInteger('id_offer');
            $table->foreign('id_offer')->references('id')->on('offers')->onDelete('cascade');
            $table->unsignedBigInteger('id_color');
            $table->foreign('id_color')->references('id')->on('colors')->onDelete('cascade');
            $table->unsignedBigInteger('id_image');
            $table->foreign('id_image')->references('id')->on('files')->onDelete('cascade');
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
        Schema::dropIfExists('has_offers');
    }
};