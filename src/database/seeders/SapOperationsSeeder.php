<?php

namespace Database\Seeders;

use Database\Factories\SapOperationFactory;
use Illuminate\Database\Seeder;
use App\Models\SapOperation;
use App\Models\OperationType;
use App\Models\Account;

class SapOperationsSeeder extends Seeder
{
    /**
     * Number of records to seed.
     *
     * @var int
     */
    public static int $count = 50;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SapOperation::factory()->count(SapOperationsSeeder::$count)->create();
    }
}
