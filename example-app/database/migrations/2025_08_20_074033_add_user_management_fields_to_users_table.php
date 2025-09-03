<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Champs pour la gestion des utilisateurs
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('phone')->nullable()->after('email');
            $table->string('linkedin_url')->nullable()->after('phone');
            $table->enum('user_type', ['admin', 'candidate'])->default('candidate')->after('linkedin_url');
            $table->boolean('is_active')->default(true)->after('user_type');
            $table->timestamp('blocked_at')->nullable()->after('is_active');
            $table->string('blocked_reason')->nullable()->after('blocked_at');
            $table->unsignedBigInteger('blocked_by')->nullable()->after('blocked_reason');

            // Index pour les performances
            $table->index(['user_type', 'is_active']);
            $table->index('email');

            // Clé étrangère pour qui a bloqué l'utilisateur
            $table->foreign('blocked_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['blocked_by']);
            $table->dropIndex(['user_type', 'is_active']);
            $table->dropIndex(['email']);
            $table->dropColumn([
                'first_name',
                'last_name',
                'phone',
                'linkedin_url',
                'user_type',
                'is_active',
                'blocked_at',
                'blocked_reason',
                'blocked_by'
            ]);
        });
    }
};
