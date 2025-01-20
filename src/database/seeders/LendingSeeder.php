<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Lending;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LendingSeeder extends Seeder
{
    /**
     * How many accounts should be generated when seeding the database.
     *
     * @var int
     */
    public static int $count = 2;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
//        Lending::factory()->count($this::$count)->create();
    }
}
