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
        Schema::table('has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('id_store')->nullable();
            $table->foreign('id_store')->references('id')->on('stores')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('has_permissions', function (Blueprint $table) {
            $table->dropForeign(['id_store']);
            $table->dropColumn('id_store');
        });
    }
};
