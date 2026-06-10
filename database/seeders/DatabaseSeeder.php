<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Kita pakai User::create langsung tanpa factory agar data tersimpan murni dan aman
        User::create([
            'name' => 'admin',
            'email' => 'admin@taptrack.local',
            'password' => Hash::make('123456'), // Password login kamu
        ]);
    }
}