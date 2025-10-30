<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer 2 admins
        User::factory()->create([
            'nom' => 'Admin',
            'prenom' => 'Principal',
            'email' => 'admin1@example.com',
            'login' => 'admin1',
            'password' => Hash::make('password'),
            'telephone' => '+221771234567',
            'actif' => true,
        ]);

        User::factory()->create([
            'nom' => 'Admin',
            'prenom' => 'Secondaire',
            'email' => 'admin2@example.com',
            'login' => 'admin2',
            'password' => Hash::make('password'),
            'telephone' => '+221771234568',
            'actif' => true,
        ]);

        // Créer 8 autres utilisateurs
        User::factory(8)->create();
    }
}
