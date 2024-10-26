<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * How many accounts should be generated when seeding the database.
     *
     * @var int
     */
    public static int $accountsCount = 5;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Account::factory()->count(AccountSeeder::$accountsCount)->create();
    }
}
