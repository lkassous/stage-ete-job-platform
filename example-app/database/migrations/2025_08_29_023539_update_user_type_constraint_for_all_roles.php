<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Supprimer l'ancienne contrainte
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_user_type_check");

        // Ajouter la nouvelle contrainte avec tous les types de rôles
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_user_type_check CHECK (user_type IN (
            'candidate',
            'super_admin',
            'admin',
            'hr_director',
            'hr_manager',
            'senior_recruiter',
            'recruiter',
            'junior_recruiter',
            'analyst',
            'viewer'
        ))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revenir à l'ancienne contrainte
        DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_user_type_check");
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_user_type_check CHECK (user_type IN ('admin', 'candidate', 'hr_manager', 'recruiter'))");
    }
};
