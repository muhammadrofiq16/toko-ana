<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin user
        User::updateOrCreate(
            ['email' => 'admin@toko.com'],
            [
                'name' => 'Administrator Toko',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // Kasir user
        User::updateOrCreate(
            ['email' => 'kasir@toko.com'],
            [
                'name' => 'Kasir 1',
                'password' => Hash::make('password'),
                'role' => 'kasir',
            ]
        );
    }
}
