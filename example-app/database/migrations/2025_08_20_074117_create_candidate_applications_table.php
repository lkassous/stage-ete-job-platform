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
        Schema::create('candidate_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('cv_file_path');
            $table->string('cover_letter_path')->nullable();
            $table->enum('status', ['pending', 'processing', 'analyzed', 'rejected'])->default('pending');
            $table->json('ai_analysis')->nullable(); // Stockage de l'analyse IA
            $table->text('admin_notes')->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamp('analyzed_at')->nullable();
            $table->timestamps();

            // Index pour les performances
            $table->index(['user_id', 'status']);
            $table->index('status');
            $table->index('submitted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidate_applications');
    }
};
