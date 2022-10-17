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
        Schema::create('details', function (Blueprint $table) {
            $table->id();
            $table->integer('amount')->default(1);
            $table->double('price')->default(1);
            $table->unsignedBigInteger('id_shape')->nullable();
            $table->foreign('id_shape')->references('id')->on('shapes');
            $table->unsignedBigInteger('id_size')->nullable();
            $table->foreign('id_size')->references('id')->on('sizes');
            $table->unsignedBigInteger('id_color')->nullable();
            $table->foreign('id_color')->references('id')->on('colors');
            $table->unsignedBigInteger('id_offer')->nullable();
            $table->foreign('id_offer')->references('id')->on('offers')->onDelete('cascade');
            $table->unsignedBigInteger('id_order');
            $table->foreign('id_order')->references('id')->on('orders')->onDelete('cascade');
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
        Schema::dropIfExists('details');
    }
};