<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JobOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'type',
        'description',
        'requirements',
        'location',
        'contract_type',
        'salary_range',
        'company_name',
        'company_description',
        'experience_level',
        'skills_required',
        'application_deadline',
        'status',
        'positions_available',
        'contact_email'
    ];

    protected $casts = [
        'skills_required' => 'array',
        'application_deadline' => 'date',
    ];

    /**
     * Get the candidates for this job offer.
     */
    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class, 'job_offer_id');
    }

    /**
     * Get active job offers
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get job offers by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}
