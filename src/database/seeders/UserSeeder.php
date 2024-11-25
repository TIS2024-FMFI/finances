<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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
        User::create([
            'email' => 'a@b.c',
            'password' => Hash::make('password'),
            'user_type' => 2,
            'password_change_required' => 0,
        ]);

        User::factory()->count($this::$userCount)->create();
    }
}
