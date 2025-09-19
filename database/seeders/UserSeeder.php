<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {

         User::updateOrCreate(
            ['email' => 'admin@minimart.com'],
            [
                 'name' => 'Admin',
            'email' => 'admin@minimart.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
                'remember_token' => Str::random(10),
            ]
        );

        // Staff user
        User::updateOrCreate(
            ['email' => 'staff@minimart.com'],
            [
                 'name' => 'Staff',
            'email' => 'staff@minimart.com',
            'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
            ]
        );
    }
}