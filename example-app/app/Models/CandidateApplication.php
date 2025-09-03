<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CandidateApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cv_file_path',
        'cover_letter_path',
        'status',
        'ai_analysis',
        'admin_notes',
        'submitted_at',
        'analyzed_at',
    ];

    protected $casts = [
        'ai_analysis' => 'array',
        'submitted_at' => 'datetime',
        'analyzed_at' => 'datetime',
    ];

    /**
     * Relation avec l'utilisateur (candidat)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scopes pour filtrer les candidatures
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAnalyzed($query)
    {
        return $query->where('status', 'analyzed');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    /**
     * Accesseurs pour les fichiers
     */
    public function getCvUrlAttribute()
    {
        return $this->cv_file_path ? asset('storage/' . $this->cv_file_path) : null;
    }

    public function getCoverLetterUrlAttribute()
    {
        return $this->cover_letter_path ? asset('storage/' . $this->cover_letter_path) : null;
    }
}
