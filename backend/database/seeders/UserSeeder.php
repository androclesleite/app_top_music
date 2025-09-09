<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@techpines.com.br',
            'password' => Hash::make('admin123456'),
        ]);

        User::create([
            'name' => 'Jansen Felipe',
            'email' => 'jansen@techpines.com.br',
            'password' => Hash::make('password123'),
        ]);
    }
}