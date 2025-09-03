<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobPosition extends Model
{
    protected $fillable = [
        'title',
        'description',
        'required_skills',
        'preferred_qualifications',
        'company_info',
        'status',
    ];

    protected $casts = [
        'required_skills' => 'array',
        'preferred_qualifications' => 'array',
    ];

    /**
     * Get the CV analyses for this job position.
     */
    public function cvAnalyses(): HasMany
    {
        return $this->hasMany(CvAnalysis::class);
    }

    /**
     * Get candidates who applied for this position.
     */
    public function candidates()
    {
        return $this->hasManyThrough(Candidate::class, CvAnalysis::class);
    }

    /**
     * Check if the position is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get requirements as a formatted string.
     */
    public function getRequirements(): string
    {
        $skills = $this->required_skills ?? [];
        return implode(', ', $skills);
    }

    /**
     * Match candidates based on skills and requirements.
     */
    public function matchCandidates()
    {
        // Logic for matching candidates will be implemented later
        return $this->cvAnalyses()->where('suitability_score', '>=', 70);
    }
}
