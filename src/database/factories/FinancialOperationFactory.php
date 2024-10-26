<?php

namespace Database\Factories;

use App\Models\AccountUser;
use App\Models\OperationType;
//use Database\Seeders\AccountSeeder;
//use Database\Seeders\OperationTypeSeeder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FinancialOperation>
 */
class FinancialOperationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $accounts_users = AccountUser::all('id');
        $operationTypes = OperationType::where('lending', '=', false)->get('id');

        return [
            'account_user_id' => $accounts_users->random()['id'],
            'title' => fake()->text(20),
            'date' => fake()->date,
            'operation_type_id' => $operationTypes->random()['id'],
            'subject' => fake()->name,
            'sum' => fake()->randomFloat(2,1,1000),
            'attachment' => fake()->unique()->filePath(),
        ];
    }
}
