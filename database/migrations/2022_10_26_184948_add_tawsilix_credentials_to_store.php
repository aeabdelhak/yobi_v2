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
        Schema::table('stores', function (Blueprint $table) {
            $table->dropForeign(['id_token']);
            $table->dropColumn('id_token');
            $table->string('token');
            $table->string('secret_token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->unsignedBigInteger('id_token');
            $table->foreign('id_token')->references('id')->on('tawsilix_accesses')->onDelete('cascade');
            $table->dropColumn('token');
            $table->dropColumn('secret_token');
        });
    }
};