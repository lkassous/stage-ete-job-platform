<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer les rôles
        $adminRole = Role::where('name', 'admin')->first();
        $hrRole = Role::where('name', 'hr_manager')->first();
        $recruiterRole = Role::where('name', 'recruiter')->first();

        // Créer l'administrateur principal
        $admin = User::firstOrCreate(
            ['email' => 'admin@cv-system.com'],
            [
                'name' => 'Administrateur Principal',
                'first_name' => 'Admin',
                'last_name' => 'Principal',
                'email' => 'admin@cv-system.com',
                'password' => Hash::make('admin123'),
                'role_id' => $adminRole?->id,
                'user_type' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Créer un responsable RH
        $hrManager = User::firstOrCreate(
            ['email' => 'rh@cv-system.com'],
            [
                'name' => 'Marie Dubois',
                'first_name' => 'Marie',
                'last_name' => 'Dubois',
                'email' => 'rh@cv-system.com',
                'password' => Hash::make('rh123'),
                'role_id' => $hrRole?->id,
                'user_type' => 'hr_manager',
                'is_active' => true,
                'phone' => '+33123456789',
                'email_verified_at' => now(),
            ]
        );

        // Créer un recruteur
        $recruiter = User::firstOrCreate(
            ['email' => 'recruteur@cv-system.com'],
            [
                'name' => 'Pierre Martin',
                'first_name' => 'Pierre',
                'last_name' => 'Martin',
                'email' => 'recruteur@cv-system.com',
                'password' => Hash::make('recruteur123'),
                'role_id' => $recruiterRole?->id,
                'user_type' => 'recruiter',
                'is_active' => true,
                'phone' => '+33987654321',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Utilisateurs avec rôles créés avec succès !');
        $this->command->info('');
        $this->command->info('=== COMPTES CRÉÉS ===');
        $this->command->info('👑 Admin: admin@cv-system.com / admin123');
        $this->command->info('👥 RH: rh@cv-system.com / rh123');
        $this->command->info('🎯 Recruteur: recruteur@cv-system.com / recruteur123');
    }
}
