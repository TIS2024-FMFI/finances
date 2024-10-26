<?php

namespace Database\Factories;

use Database\Seeders\AccountSeeder;
use Database\Seeders\FinancialOperationSeeder;
use Database\Seeders\OperationTypeSeeder;
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
        return [
            'expected_date_of_return' => fake()->date
        ];
    }
}
