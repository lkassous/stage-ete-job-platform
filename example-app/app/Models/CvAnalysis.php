<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CvAnalysis extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidate_id',
        'candidate_application_id',
        'job_position_id',
        'profile_summary',
        'key_skills',
        'education',
        'experience',
        'languages',
        'strengths',
        'weaknesses',
        'job_match_score',
        'job_match_analysis',
        'recommendations',
        'overall_rating',
        'next_steps',
        'suitability_analysis',
        'suitability_score',
        'raw_ai_response',
        'analysis_status',
        'analyzed_at',
        'tokens_used',
        'cost_estimate',
    ];

    protected $casts = [
        'key_skills' => 'array',
        'education' => 'array',
        'experience' => 'array',
        'languages' => 'array',
        'strengths' => 'array',
        'weaknesses' => 'array',
        'recommendations' => 'array',
        'next_steps' => 'array',
        'raw_ai_response' => 'array',
        'analyzed_at' => 'datetime',
        'job_match_score' => 'integer',
        'tokens_used' => 'integer',
        'cost_estimate' => 'decimal:4',
    ];

    /**
     * Get the candidate application that owns this analysis.
     */
    public function candidateApplication(): BelongsTo
    {
        return $this->belongsTo(CandidateApplication::class);
    }

    /**
     * Get the candidate that owns this analysis.
     */
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    /**
     * Get the job position for this analysis.
     */
    public function jobPosition(): BelongsTo
    {
        return $this->belongsTo(JobPosition::class);
    }

    /**
     * Get all AI processing logs for this analysis.
     */
    public function aiProcessingLogs(): HasMany
    {
        return $this->hasMany(AiProcessingLog::class);
    }

    /**
     * Check if the analysis is completed.
     */
    public function isCompleted(): bool
    {
        return $this->analysis_status === 'completed';
    }

    /**
     * Get the suitability score as a percentage.
     */
    public function getSuitabilityPercentageAttribute(): string
    {
        return $this->suitability_score ? $this->suitability_score . '%' : 'N/A';
    }

    /**
     * Process this CV analysis with AI using OpenAI service.
     */
    public function processWithAI(): bool
    {
        try {
            $this->update(['analysis_status' => 'processing']);

            $openAIService = app(\App\Services\OpenAIService::class);

            // Get CV and cover letter text from candidate application
            $candidateApp = $this->candidateApplication;
            $cvText = $candidateApp->cv_text ?? 'CV text not available';
            $coverLetterText = $candidateApp->cover_letter_text ?? '';
            $jobDescription = $this->jobPosition->description ?? '';

            // Analyze with OpenAI
            $result = $openAIService->analyzeCv($cvText, $coverLetterText, $jobDescription);

            if ($result['success']) {
                $this->markAsCompleted($result['analysis'], $result['usage']);
                return true;
            } else {
                $this->markAsFailed($result['error']);
                return false;
            }

        } catch (\Exception $e) {
            $this->markAsFailed($e->getMessage());
            return false;
        }
    }

    /**
     * Mark analysis as completed with OpenAI data.
     */
    public function markAsCompleted(array $analysisData, array $usage = null): void
    {
        $this->update([
            'profile_summary' => $analysisData['profile_summary'] ?? null,
            'key_skills' => $analysisData['key_skills'] ?? [],
            'education' => $analysisData['education'] ?? [],
            'experience' => $analysisData['experience'] ?? [],
            'languages' => $analysisData['languages'] ?? [],
            'strengths' => $analysisData['strengths'] ?? [],
            'weaknesses' => $analysisData['weaknesses'] ?? [],
            'job_match_score' => $analysisData['job_match_score'] ?? null,
            'job_match_analysis' => $analysisData['job_match_analysis'] ?? null,
            'recommendations' => $analysisData['recommendations'] ?? [],
            'overall_rating' => $analysisData['overall_rating'] ?? null,
            'next_steps' => $analysisData['next_steps'] ?? [],
            'suitability_analysis' => $analysisData['job_match_analysis'] ?? null,
            'suitability_score' => $analysisData['job_match_score'] ?? null,
            'raw_ai_response' => $analysisData,
            'analysis_status' => 'completed',
            'analyzed_at' => now(),
            'tokens_used' => $usage['total_tokens'] ?? null,
            'cost_estimate' => $this->calculateCost($usage['total_tokens'] ?? 0),
        ]);
    }

    /**
     * Mark analysis as failed.
     */
    public function markAsFailed(string $error): void
    {
        $this->update([
            'analysis_status' => 'failed',
            'raw_ai_response' => ['error' => $error],
            'analyzed_at' => now(),
        ]);
    }

    /**
     * Calculate estimated cost based on tokens used.
     */
    private function calculateCost(int $tokens): float
    {
        // GPT-4o-mini pricing: $0.00015 per 1K input tokens, $0.0006 per 1K output tokens
        // Approximation: assume 70% input, 30% output
        $inputTokens = $tokens * 0.7;
        $outputTokens = $tokens * 0.3;

        $inputCost = ($inputTokens / 1000) * 0.00015;
        $outputCost = ($outputTokens / 1000) * 0.0006;

        return round($inputCost + $outputCost, 4);
    }

    /**
     * Check if analysis is pending.
     */
    public function isPending(): bool
    {
        return $this->analysis_status === 'pending';
    }

    /**
     * Check if analysis is processing.
     */
    public function isProcessing(): bool
    {
        return $this->analysis_status === 'processing';
    }

    /**
     * Check if analysis failed.
     */
    public function isFailed(): bool
    {
        return $this->analysis_status === 'failed';
    }
}
