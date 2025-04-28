<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{

    public function run(): void
    {
        $users = [
            [
                'name' => 'Ismail Basbous',
                'email' => 'ismail@gmail.com',
                'is_admin' => true,
                'password' => Hash::make('12345678'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Regular User',
                'email' => 'user@example.com',
                'is_admin' => false,
                'password' => Hash::make('12345678'),
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $user) {
            DB::table('users')->insert($user);
        }
    }
}
