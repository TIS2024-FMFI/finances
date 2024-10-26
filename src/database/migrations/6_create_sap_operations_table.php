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
        Schema::create('sap_operations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('operation_type_id');
            $table->foreign('operation_type_id')->references('id')->on('operation_types')->cascadeOnDelete();
            $table->string('title');
            $table->date('date');
            $table->string('subject');
            $table->Decimal('sum',10,2);
            $table->unsignedBigInteger('sap_id');
            $table->string('account_sap_id');
            $table->foreign('account_sap_id')->references('sap_id')->on('accounts')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sap_operations');
    }
};
