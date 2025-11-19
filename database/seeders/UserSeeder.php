<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@ecommerce.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Vendor users
        User::create([
            'name' => 'Apple Inc',
            'email' => 'vendor@apple.com',
            'password' => Hash::make('password123'),
            'role' => 'vendor',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Samsung Electronics',
            'email' => 'vendor@samsung.com',
            'password' => Hash::make('password123'),
            'role' => 'vendor',
            'email_verified_at' => now(),
        ]);

        // Customer users
        User::factory()->count(10)->create([
            'role' => 'customer',
        ]);
    }
}
