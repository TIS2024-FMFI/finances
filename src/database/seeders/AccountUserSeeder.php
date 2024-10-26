<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Account;
use App\Models\user;

class AccountUserSeeder extends Seeder
{


    /**
     * How many operations should be generated when seeding the database.
     *
     * @var int
     */
    public static int $accountsCount = 6;
    public static int $usersCount = 3;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $accounts = Account::factory()->count(AccountUserSeeder::$accountsCount)->create();
        $users = User::factory()->count(AccountUserSeeder::$usersCount)->create();
        
        $users->each(function (User $user, int $key) use ($accounts) {
            $user->accounts()->attach(
                $accounts->random(rand(1, 3))->pluck('id')->toArray(),
                ['account_title' => 'Account']
            ); 
        });
    }
}
