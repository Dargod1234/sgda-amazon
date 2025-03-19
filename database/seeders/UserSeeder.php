<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User; // Asegúrate de tener el modelo User importado.

class UserSeeder extends Seeder
{
    public function run()
    {
        // Crear el usuario admin
        User::firstOrCreate([
            'email' => 'admin@admin.com',
        ], [
            'name' => 'admin',
            'password' => Hash::make('12345678'),
            'role' => 'admin', // Asigna el rol correspondiente
        ]);

        // Crear el usuario moderador
        User::firstOrCreate([
            'email' => 'user2@user.com',
        ], [
            'name' => 'user2',
            'password' => Hash::make('12345678'),
            'role' => 'moderator', // Asigna el rol correspondiente
        ]);

        // Crear el usuario normal
        User::firstOrCreate([
            'email' => 'user@user.com',
        ], [
            'name' => 'user',
            'password' => Hash::make('12345678'),
            'role' => 'user', // Asigna el rol correspondiente
        ]);
    }
}
