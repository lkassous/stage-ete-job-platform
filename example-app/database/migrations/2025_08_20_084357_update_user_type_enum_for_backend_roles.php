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
        // Pour PostgreSQL, nous devons modifier l'enum en utilisant des commandes SQL brutes
        DB::statement("ALTER TABLE users DROP CONSTRAINT users_user_type_check");
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_user_type_check CHECK (user_type IN ('admin', 'candidate', 'hr_manager', 'recruiter'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revenir à l'enum original
        DB::statement("ALTER TABLE users DROP CONSTRAINT users_user_type_check");
        DB::statement("ALTER TABLE users ADD CONSTRAINT users_user_type_check CHECK (user_type IN ('admin', 'candidate'))");
    }
};
