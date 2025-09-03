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
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->string('nom'); // Last Name
            $table->string('prenom'); // First Name
            $table->string('email')->unique();
            $table->string('telephone')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('cv_path'); // Path to CV PDF file
            $table->string('cover_letter_path')->nullable(); // Path to cover letter PDF
            $table->enum('status', ['pending', 'analyzed', 'reviewed', 'rejected', 'accepted'])->default('pending');
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
