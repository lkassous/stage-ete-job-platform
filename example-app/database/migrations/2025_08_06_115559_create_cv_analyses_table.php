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
        Schema::create('cv_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->onDelete('cascade');
            $table->foreignId('job_position_id')->nullable()->constrained()->onDelete('set null');
            $table->text('profile_summary')->nullable(); // AI-generated profile summary
            $table->json('key_skills')->nullable(); // Array of key skills
            $table->json('education')->nullable(); // Education details
            $table->json('experience')->nullable(); // Work experience
            $table->text('suitability_analysis')->nullable(); // Job suitability analysis
            $table->integer('suitability_score')->nullable(); // Score from 1-100
            $table->json('raw_ai_response')->nullable(); // Full AI response for debugging
            $table->enum('analysis_status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->timestamp('analyzed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cv_analyses');
    }
};
