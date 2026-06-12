<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        /* User::create([
            'name' => 'User',
            'email' => 'user@email.com',
            'role' => 'user',
            'password' => Hash::make('Password123'),
            'email_verified_at' => now(),
        ]); */
    }
}