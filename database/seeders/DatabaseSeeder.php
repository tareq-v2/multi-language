<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        User::create([
            'name' => 'John Doe',
            'email' => 'john1@example.com',
            'password' => Hash::make('password123'),
            'is_online' => true,
        ]);

        User::create([
            'name' => 'Jane Smith',
            'email' => 'jane1@example.com',
            'password' => Hash::make('password123'),
            'is_online' => false,
        ]);

        User::create([
            'name' => 'Bob Johnson',
            'email' => 'bob1@example.com',
            'password' => Hash::make('password123'),
            'is_online' => true,
        ]);
    }
}
