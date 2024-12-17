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
        Schema::create('lendings', function (Blueprint $table) {
            $table->id();
            $table->date('expected_date_of_return')->nullable();
            $table->smallInteger('status');
            $table->unsignedBigInteger('host_id');
            $table->foreign('host_id')->references('id')->on('account_user')->cascadeOnDelete();
            $table->unsignedBigInteger('client_id');
            $table->foreign('client_id')->references('id')->on('account_user')->cascadeOnDelete();


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lendings');
    }
};
