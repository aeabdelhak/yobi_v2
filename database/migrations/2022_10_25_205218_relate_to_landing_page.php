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
        Schema::table('offers', function (Blueprint $table) {
            $table->dropForeign(['id_shape']);
            $table->dropColumn('id_shape');
            $table->unsignedBigInteger('id_landing_page')->nullable();
            $table->foreign('id_landing_page')->references('id')->on('landing_pages');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->unsignedBigInteger('id_shape');
            $table->foreign('id_shape')->references('id')->on('shapes')->onDelete('cascade');
            $table->dropForeign(['id_landing_page']);
            $table->dropColumn('id_landing_page');

        });
    }
};