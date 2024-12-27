<?php

namespace Database\Factories;

use App\Models\AccountUser;
use App\Models\Lending;
use App\Models\OperationType;
//use Database\Seeders\AccountSeeder;
//use Database\Seeders\OperationTypeSeeder;
use App\Models\SapOperation;
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

        $sapOperations = SapOperation::all('id');
        $sapOperationId = $sapOperations->isNotEmpty()
            ? $sapOperations->random()->id
            : null;

        $lendings = Lending::all('id');
        $lendingId = $lendings->isNotEmpty()
            ? $lendings->random()->id
            : null;

        $attachmentMessage = "Toto je príloha k finančnej operácii.";


        return [
            'account_user_id' => $accounts_users->random()['id'],
            'title' => fake()->text(20),
            'date' => fake()->date,
            'operation_type_id' => $operationTypes->random()['id'],
            'subject' => fake()->name,
            'sum' => fake()->randomFloat(2,1,1000),
            'sap_operation_id' => $sapOperationId,
            'attachment' => $attachmentMessage,
            'status' => $this->faker->numberBetween(1, 3),
            'lending_id' => $lendingId,
        ];
    }
}
