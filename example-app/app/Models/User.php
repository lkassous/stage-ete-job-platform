<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use App\Notifications\ResetPasswordNotification;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'linkedin_url',
        'user_type',
        'is_active',
        'blocked_at',
        'blocked_reason',
        'blocked_by',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'blocked_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the role that belongs to the user.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->role) {
            return false;
        }

        // Admin has all permissions
        if ($this->role->hasPermission('*')) {
            return true;
        }

        return $this->role->hasPermission($permission);
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->role && $this->role->name === 'admin';
    }

    /**
     * Check if user is HR manager.
     */
    public function isHrManager(): bool
    {
        return $this->role && $this->role->name === 'hr_manager';
    }

    /**
     * Check if user is recruiter.
     */
    public function isRecruiter(): bool
    {
        return $this->role && $this->role->name === 'recruiter';
    }

    /**
     * Relation avec les candidatures
     */
    public function candidateApplications(): HasMany
    {
        return $this->hasMany(CandidateApplication::class);
    }

    /**
     * Relation avec l'utilisateur qui a bloqué ce compte
     */
    public function blockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }

    /**
     * Relation avec les utilisateurs bloqués par cet utilisateur
     */
    public function blockedUsers(): HasMany
    {
        return $this->hasMany(User::class, 'blocked_by');
    }

    /**
     * Vérifier si l'utilisateur est un candidat
     */
    public function isCandidate(): bool
    {
        return $this->user_type === 'candidate';
    }

    /**
     * Vérifier si l'utilisateur est un admin (nouveau système)
     */
    public function isAdminUser(): bool
    {
        return $this->user_type === 'admin';
    }

    /**
     * Vérifier si l'utilisateur est actif
     */
    public function isActive(): bool
    {
        return $this->is_active && !$this->blocked_at;
    }

    /**
     * Bloquer l'utilisateur
     */
    public function block(string $reason = null, User $blockedBy = null): void
    {
        $this->update([
            'is_active' => false,
            'blocked_at' => now(),
            'blocked_reason' => $reason,
            'blocked_by' => $blockedBy?->id,
        ]);
    }

    /**
     * Débloquer l'utilisateur
     */
    public function unblock(): void
    {
        $this->update([
            'is_active' => true,
            'blocked_at' => null,
            'blocked_reason' => null,
            'blocked_by' => null,
        ]);
    }

    /**
     * Scope pour les utilisateurs actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->whereNull('blocked_at');
    }

    /**
     * Scope pour les utilisateurs bloqués
     */
    public function scopeBlocked($query)
    {
        return $query->where('is_active', false)->orWhereNotNull('blocked_at');
    }

    /**
     * Scope pour les candidats
     */
    public function scopeCandidates($query)
    {
        return $query->where('user_type', 'candidate');
    }

    /**
     * Scope pour les admins
     */
    public function scopeAdmins($query)
    {
        return $query->where('user_type', 'admin');
    }

    /**
     * Accesseur pour le nom complet
     */
    public function getFullNameAttribute(): string
    {
        if ($this->first_name && $this->last_name) {
            return $this->first_name . ' ' . $this->last_name;
        }
        return $this->name ?? '';
    }

    /**
     * Send the password reset notification.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
