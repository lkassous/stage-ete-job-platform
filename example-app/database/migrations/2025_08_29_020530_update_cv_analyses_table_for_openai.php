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
        Schema::table('cv_analyses', function (Blueprint $table) {
            // Ajouter seulement les colonnes qui n'existent pas encore
            if (!Schema::hasColumn('cv_analyses', 'languages')) {
                $table->json('languages')->nullable()->after('experience');
            }
            if (!Schema::hasColumn('cv_analyses', 'strengths')) {
                $table->json('strengths')->nullable()->after('experience');
            }
            if (!Schema::hasColumn('cv_analyses', 'weaknesses')) {
                $table->json('weaknesses')->nullable()->after('experience');
            }
            if (!Schema::hasColumn('cv_analyses', 'job_match_score')) {
                $table->integer('job_match_score')->nullable()->after('experience');
            }
            if (!Schema::hasColumn('cv_analyses', 'job_match_analysis')) {
                $table->text('job_match_analysis')->nullable()->after('experience');
            }
            if (!Schema::hasColumn('cv_analyses', 'overall_rating')) {
                $table->string('overall_rating', 1)->nullable()->after('experience');
            }
            if (!Schema::hasColumn('cv_analyses', 'next_steps')) {
                $table->json('next_steps')->nullable()->after('experience');
            }
            if (!Schema::hasColumn('cv_analyses', 'cost_estimate')) {
                $table->decimal('cost_estimate', 8, 4)->nullable()->after('analyzed_at');
            }

            // Mise à jour de la référence candidate_id vers candidate_application_id
            if (!Schema::hasColumn('cv_analyses', 'candidate_application_id')) {
                $table->unsignedBigInteger('candidate_application_id')->nullable()->after('id');
                $table->foreign('candidate_application_id')->references('id')->on('candidate_applications')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cv_analyses', function (Blueprint $table) {
            // Supprimer seulement les colonnes que nous avons ajoutées
            $columnsToCheck = [
                'languages', 'strengths', 'weaknesses', 'job_match_score',
                'job_match_analysis', 'overall_rating', 'next_steps', 'cost_estimate'
            ];

            foreach ($columnsToCheck as $column) {
                if (Schema::hasColumn('cv_analyses', $column)) {
                    $table->dropColumn($column);
                }
            }

            // Supprimer la foreign key et la colonne candidate_application_id si elle existe
            if (Schema::hasColumn('cv_analyses', 'candidate_application_id')) {
                $table->dropForeign(['candidate_application_id']);
                $table->dropColumn('candidate_application_id');
            }
        });
    }
};
