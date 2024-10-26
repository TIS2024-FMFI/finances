<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * The number of random users to create.
     * 
     * @var int
     */
    private static int $userCount = 10;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::firstOrCreate([ 'email' => 'a@b.c' ]);

        User::factory()->count($this::$userCount)->create();
    }
}
