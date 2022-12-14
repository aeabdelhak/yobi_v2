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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('city');
            $table->string('address');
            $table->integer('status')->default(1);
            $table->dateTime('status_date')->nullable();
            $table->string('phone');
            $table->boolean('paid')->default(false);
            $table->unsignedBigInteger('id_landing_page');
            $table->foreign('id_landing_page')->references('id')->on('landing_pages')->onDelete('cascade');
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
        Schema::dropIfExists('orders');
    }
};