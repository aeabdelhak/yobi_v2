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
        Schema::create('landing_pages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->string('product_name');
            $table->text('product_description');
            $table->string('domain')->unique();
            $table->integer('status')->default(1);
            $table->unsignedBigInteger('id_pallete');
            $table->foreign('id_pallete')->references('id')->on('color_palettes');
            $table->unsignedBigInteger('id_poster');
            $table->foreign('id_poster')->references('id')->on('files');
            $table->unsignedBigInteger('id_store');
            $table->foreign('id_store')->references('id')->on('stores')->onDelete('cascade');
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
        Schema::dropIfExists('landing_pages');
    }
};