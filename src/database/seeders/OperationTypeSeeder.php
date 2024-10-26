<?php

namespace Database\Seeders;


use App\Models\OperationType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OperationTypeSeeder extends Seeder
{

    /**
     * How many operation types should be generated when seeding the database.
     *
     * @var int
     */
    public static int $operationTypesCount = 10;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        OperationType::factory()->count(OperationTypeSeeder::$operationTypesCount)->create();
    }
}
