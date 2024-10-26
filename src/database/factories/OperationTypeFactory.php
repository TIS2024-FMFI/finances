<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OperationType>
 */
class OperationTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => fake()->unique()->text(10),
            'expense' => fake()->boolean(),
            'lending' => fake()->boolean(0.2),
            'repayment' => fake()->boolean(0.2)
        ];
    }
}
