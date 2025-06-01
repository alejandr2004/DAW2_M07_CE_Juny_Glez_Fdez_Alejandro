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
        // Crear usuario administrador
        User::create([
            'name' => 'Admin',
            'email' => 'admin@myspotify.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'active' => true,
        ]);

        // Crear usuario cliente de ejemplo
        User::create([
            'name' => 'Cliente',
            'email' => 'cliente@example.com',
            'password' => Hash::make('password'),
            'role' => 'client',
            'active' => true,
        ]);

        // Crear algunos usuarios más usando factory
        User::factory(5)->create([
            'role' => 'client',
            'active' => true,
        ]);
    }
}
