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
        // Lista de usuarios a crear
        $users = [
            // Administrador
            [
                'name' => 'Admin',
                'email' => 'admin@myspotify.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'active' => true,
            ],
            // Clientes con nombres españoles
            [
                'name' => 'María García',
                'email' => 'maria@example.com',
                'password' => Hash::make('password'),
                'role' => 'client',
                'active' => true,
            ],
            [
                'name' => 'Carlos Rodríguez',
                'email' => 'carlos@example.com',
                'password' => Hash::make('password'),
                'role' => 'client',
                'active' => true,
            ],
            [
                'name' => 'Ana Martínez',
                'email' => 'ana@example.com',
                'password' => Hash::make('password'),
                'role' => 'client',
                'active' => true,
            ],
            [
                'name' => 'Javier López',
                'email' => 'javier@example.com',
                'password' => Hash::make('password'),
                'role' => 'client',
                'active' => true,
            ],
            [
                'name' => 'Lucía Fernández',
                'email' => 'lucia@example.com',
                'password' => Hash::make('password'),
                'role' => 'client',
                'active' => true,
            ],
            [
                'name' => 'David González',
                'email' => 'david@example.com',
                'password' => Hash::make('password'),
                'role' => 'client',
                'active' => true,
            ],
            [
                'name' => 'Carmen Díaz',
                'email' => 'carmen@example.com',
                'password' => Hash::make('password'),
                'role' => 'client',
                'active' => true,
            ],
            [
                'name' => 'Miguel Pérez',
                'email' => 'miguel@example.com',
                'password' => Hash::make('password'),
                'role' => 'client',
                'active' => true,
            ],
            [
                'name' => 'Elena Sánchez',
                'email' => 'elena@example.com',
                'password' => Hash::make('password'),
                'role' => 'client',
                'active' => true,
            ],
            [
                'name' => 'Pablo Romero',
                'email' => 'pablo@example.com',
                'password' => Hash::make('password'),
                'role' => 'client',
                'active' => true,
            ],
        ];

        // Crear usuarios si no existen
        foreach ($users as $userData) {
            User::firstOrCreate(
                // Buscar por email
                ['email' => $userData['email']],
                // Datos a crear si no existe
                $userData
            );
        }
        
        $this->command->info('Usuarios creados correctamente. Utiliza estas credenciales para acceder:');
        $this->command->info('- Admin: admin@myspotify.com / password');
        $this->command->info('- Cliente: maria@example.com / password (u otro de los usuarios creados)');
    }
}
