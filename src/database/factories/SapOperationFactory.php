<?php

namespace Database\Factories;

use App\Models\OperationType;
use App\Models\SapOperation;
//use Database\Seeders\AccountSeeder;
//use Database\Seeders\OperationTypeSeeder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class SapOperationFactory extends Factory
{


    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $operationTypes = OperationType::where('name', '=', SapOperation::$sap_operation_type_name)->get('id');

        return [
            'operation_type_id' => $operationTypes->random()['id'],
            'title' => fake()->text(20),
            'date' => fake()->date,
            'subject' => fake()->name,
            'sum' => fake()->randomFloat(2,1,1000),
            'sap_id' => fake()->randomNumber(5),
            'account_sap_id' => fake()->text(20)
            /*
            HINT:
            $table->unsignedBigInteger('operation_type_id');
            $table->string('title');
            $table->date('date');
            $table->string('subject');
            $table->unsignedDecimal('sum',10,2);
            $table->integer('sap_id')->nullable();*/
        ];
    }
}
