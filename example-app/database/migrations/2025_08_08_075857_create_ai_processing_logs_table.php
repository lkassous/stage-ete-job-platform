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
        Schema::create('ai_processing_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cv_analysis_id')->constrained()->onDelete('cascade');
            $table->string('api_provider')->default('openai');
            $table->text('request_payload')->nullable();
            $table->text('response_payload')->nullable();
            $table->integer('tokens_used')->nullable();
            $table->float('processing_time')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_processing_logs');
    }
};
