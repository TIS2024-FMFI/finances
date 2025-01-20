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
            $table->unsignedBigInteger('host_id');
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('operation_client_id')->unique();
            $table->unsignedBigInteger('operation_host_id')->unique();
            $table->boolean('isRepayed')->default(false);
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
