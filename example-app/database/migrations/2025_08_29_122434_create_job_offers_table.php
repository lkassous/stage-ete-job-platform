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
        Schema::create('job_offers', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Titre du poste
            $table->enum('type', ['emploi', 'stage']); // Type d'offre
            $table->text('description'); // Description détaillée
            $table->text('requirements'); // Exigences/compétences requises
            $table->string('location'); // Lieu de travail
            $table->enum('contract_type', ['CDI', 'CDD', 'Stage', 'Freelance', 'Alternance']); // Type de contrat
            $table->string('salary_range')->nullable(); // Fourchette de salaire
            $table->string('company_name'); // Nom de l'entreprise
            $table->text('company_description')->nullable(); // Description de l'entreprise
            $table->enum('experience_level', ['junior', 'intermediate', 'senior', 'expert']); // Niveau d'expérience
            $table->json('skills_required')->nullable(); // Compétences requises (JSON)
            $table->date('application_deadline')->nullable(); // Date limite de candidature
            $table->enum('status', ['active', 'inactive', 'closed'])->default('active'); // Statut de l'offre
            $table->integer('positions_available')->default(1); // Nombre de postes disponibles
            $table->string('contact_email')->nullable(); // Email de contact
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_offers');
    }
};
