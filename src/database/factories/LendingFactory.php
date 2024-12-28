<?php

namespace Database\Factories;

use App\Models\AccountUser;
use App\Models\FinancialOperation;
use App\Models\Lending;
use App\Models\OperationType;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FinancialOperation>
 */
class LendingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {

        $user_host= AccountUser::all('id')->random()['id'];
        $user_client = AccountUser::where('user_id', '!=', $user_host)->get('id')->random()['id'];

        $usedOperationIds = Lending::pluck('operation_id')->toArray();
        $operation = FinancialOperation::whereNotIn('id', $usedOperationIds)->inRandomOrder()->firstOrFail()->id;

        return [
            'expected_date_of_return' => fake()->date,
            "host_id"=>$user_host,
            "client_id"=>$user_client,
        ];
    }
}
