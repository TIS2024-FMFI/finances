<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    /**
     * The name of the database connection.
     *
     * @var string
     */


    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('db2')->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('password')->default(Hash::make('password'));
            $table->boolean('password_change_required')->default(true);
            $table->boolean('user_type')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
