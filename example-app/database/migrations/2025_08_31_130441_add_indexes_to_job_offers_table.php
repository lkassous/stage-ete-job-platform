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
        Schema::table('job_offers', function (Blueprint $table) {
            // Index pour optimiser les requêtes sur le status
            $table->index('status');

            // Index pour optimiser le tri par date de création
            $table->index('created_at');

            // Index composé pour les requêtes publiques (status + created_at)
            $table->index(['status', 'created_at']);

            // Index pour les filtres par type
            $table->index('type');

            // Index pour les filtres par localisation
            $table->index('location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_offers', function (Blueprint $table) {
            // Supprimer les index
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['type']);
            $table->dropIndex(['location']);
        });
    }
};
