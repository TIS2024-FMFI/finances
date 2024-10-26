<?php

namespace Database\Seeders;

use App\Models\FinancialOperation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FinancialOperationSeeder extends Seeder
{

    /**
     * How many operations should be generated when seeding the database.
     *
     * @var int
     */
    public static int $operationsCount = 100;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        FinancialOperation::factory()->count(FinancialOperationSeeder::$operationsCount)->create();
    }
}
