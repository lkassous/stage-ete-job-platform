<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CandidateFile extends Model
{
    protected $fillable = [
        'candidate_id',
        'original_name',
        'file_path',
        'file_type',
        'file_size',
        'mime_type',
        'file_category',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    /**
     * Get the candidate that owns this file.
     */
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    /**
     * Get the full URL to access the file.
     */
    public function getUrl(): string
    {
        return Storage::url($this->file_path);
    }

    /**
     * Extract text content from PDF file.
     */
    public function extractText(): ?string
    {
        // Implementation will be added later with PDF parsing library
        return null;
    }

    /**
     * Validate file format and size.
     */
    public function validateFile(): bool
    {
        // Check if file exists
        if (!Storage::exists($this->file_path)) {
            return false;
        }

        // Check file size (max 10MB)
        if ($this->file_size > 10 * 1024 * 1024) {
            return false;
        }

        // Check MIME type for PDF
        return in_array($this->mime_type, ['application/pdf']);
    }

    /**
     * Check if this is a CV file.
     */
    public function isCv(): bool
    {
        return $this->file_category === 'cv';
    }

    /**
     * Check if this is a cover letter.
     */
    public function isCoverLetter(): bool
    {
        return $this->file_category === 'cover_letter';
    }

    /**
     * Get human readable file size.
     */
    public function getHumanReadableSize(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
