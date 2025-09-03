<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiProcessingLog extends Model
{
    protected $fillable = [
        'cv_analysis_id',
        'api_provider',
        'request_payload',
        'response_payload',
        'tokens_used',
        'processing_time',
        'status',
        'error_message',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
        'processing_time' => 'float',
        'tokens_used' => 'integer',
    ];

    /**
     * Get the CV analysis that owns this log.
     */
    public function cvAnalysis(): BelongsTo
    {
        return $this->belongsTo(CvAnalysis::class);
    }

    /**
     * Track API usage and costs.
     */
    public function trackUsage(): array
    {
        return [
            'tokens' => $this->tokens_used ?? 0,
            'processing_time' => $this->processing_time ?? 0,
            'cost_estimate' => $this->calculateCost(),
        ];
    }

    /**
     * Log error information.
     */
    public function logError(string $message): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $message,
            'processed_at' => now(),
        ]);
    }

    /**
     * Calculate estimated cost based on tokens used.
     */
    private function calculateCost(): float
    {
        if (!$this->tokens_used) {
            return 0.0;
        }

        // OpenAI GPT-4 pricing (approximate)
        $costPerToken = 0.00003; // $0.03 per 1K tokens
        return $this->tokens_used * $costPerToken;
    }

    /**
     * Check if processing was successful.
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if processing failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Get processing duration in seconds.
     */
    public function getProcessingDuration(): ?float
    {
        return $this->processing_time;
    }
}
