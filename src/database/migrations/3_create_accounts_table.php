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
//         Schema::connection('db2')->create('accounts', function (Blueprint $table) {
//             $table->id();
//             $table->string('sap_id')->unique();
//             $table->string('name')->default("No-Name");
//             $table->string('spravca_id')->default(1);
//         });

/*        Schema::connection('db2')->create('spp_symbols', function (Blueprint $table) {
            $table->id();
            $table->string('spp_symbol')->unique();
            $table->string('guarantee')->default(1);
}); */
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
     //   Schema::dropIfExists('accounts');
    }
};
