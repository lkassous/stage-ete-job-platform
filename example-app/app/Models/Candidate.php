<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Candidate extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'telephone',
        'linkedin_url',
        'cv_path',
        'cover_letter_path',
        'status',
        'submitted_at',
        'job_offer_id',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    /**
     * Get the CV analysis for this candidate.
     */
    public function cvAnalysis(): HasOne
    {
        return $this->hasOne(CvAnalysis::class);
    }

    /**
     * Get all CV analyses for this candidate (history).
     */
    public function cvAnalyses(): HasMany
    {
        return $this->hasMany(CvAnalysis::class);
    }

    /**
     * Get the job offer this candidate applied to.
     */
    public function jobOffer(): BelongsTo
    {
        return $this->belongsTo(JobOffer::class);
    }

    /**
     * Get all files uploaded by this candidate.
     */
    public function files(): HasMany
    {
        return $this->hasMany(CandidateFile::class);
    }

    /**
     * Get CV files only.
     */
    public function cvFiles(): HasMany
    {
        return $this->files()->where('file_category', 'cv');
    }

    /**
     * Get cover letter files only.
     */
    public function coverLetterFiles(): HasMany
    {
        return $this->files()->where('file_category', 'cover_letter');
    }

    /**
     * Get the full name of the candidate.
     */
    public function getFullNameAttribute(): string
    {
        return $this->prenom . ' ' . $this->nom;
    }

    /**
     * Check if the candidate has been analyzed by AI.
     */
    public function isAnalyzed(): bool
    {
        return $this->cvAnalysis && $this->cvAnalysis->analysis_status === 'completed';
    }

    /**
     * Upload files for this candidate.
     */
    public function uploadFiles(array $files): array
    {
        $uploadedFiles = [];

        foreach ($files as $file) {
            // File upload logic will be implemented later
            $uploadedFiles[] = $this->files()->create([
                'original_name' => $file['original_name'],
                'file_path' => $file['path'],
                'file_type' => $file['type'],
                'file_size' => $file['size'],
                'mime_type' => $file['mime_type'],
                'file_category' => $file['category'],
                'uploaded_at' => now(),
            ]);
        }

        return $uploadedFiles;
    }

    /**
     * Get the latest CV analysis.
     */
    public function getLatestAnalysis(): ?CvAnalysis
    {
        return $this->cvAnalyses()->latest()->first();
    }

    /**
     * Get suitability score for a specific job position.
     */
    public function getSuitabilityScore(JobPosition $jobPosition): ?int
    {
        $analysis = $this->cvAnalyses()
            ->where('job_position_id', $jobPosition->id)
            ->where('analysis_status', 'completed')
            ->first();

        return $analysis?->suitability_score;
    }
}
