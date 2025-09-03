<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'permissions', // Garde pour compatibilité
        'is_active',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get all users with this role.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get all permissions for this role.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    /**
     * Check if role has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        // Vérifier d'abord la permission spéciale '*' (accès complet)
        if ($this->permissions()->where('name', '*')->exists()) {
            return true;
        }

        // Vérifier la permission spécifique
        if ($this->permissions()->where('name', $permission)->exists()) {
            return true;
        }

        // Fallback vers l'ancien système (array dans la colonne permissions)
        $legacyPermissions = $this->permissions_array ?? [];
        return in_array($permission, $legacyPermissions) || in_array('*', $legacyPermissions);
    }

    /**
     * Assign a permission to this role.
     */
    public function givePermission(string|Permission $permission): void
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->first();
        }

        if ($permission && !$this->permissions()->where('permission_id', $permission->id)->exists()) {
            $this->permissions()->attach($permission->id);
        }
    }

    /**
     * Remove a permission from this role.
     */
    public function revokePermission(string|Permission $permission): void
    {
        if (is_string($permission)) {
            $permission = Permission::where('name', $permission)->first();
        }

        if ($permission) {
            $this->permissions()->detach($permission->id);
        }
    }

    /**
     * Sync permissions for this role.
     */
    public function syncPermissions(array $permissions): void
    {
        $permissionIds = [];

        foreach ($permissions as $permission) {
            if (is_string($permission)) {
                $permissionModel = Permission::where('name', $permission)->first();
                if ($permissionModel) {
                    $permissionIds[] = $permissionModel->id;
                }
            } elseif ($permission instanceof Permission) {
                $permissionIds[] = $permission->id;
            } elseif (is_numeric($permission)) {
                $permissionIds[] = $permission;
            }
        }

        $this->permissions()->sync($permissionIds);
    }

    /**
     * Get permissions as array (for compatibility).
     */
    public function getPermissionsArrayAttribute(): array
    {
        return $this->permissions()->pluck('name')->toArray();
    }

    /**
     * Check if role is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Default roles for CV Filtering System.
     */
    public static function getDefaultRoles(): array
    {
        return [
            [
                'name' => 'super_admin',
                'display_name' => 'Super Administrateur',
                'description' => 'Accès complet au système, gestion des utilisateurs et configuration',
                'permissions' => ['*'],
                'is_active' => true,
            ],
            [
                'name' => 'admin',
                'display_name' => 'Administrateur',
                'description' => 'Gestion complète des candidatures et utilisateurs',
                'permissions' => [
                    'dashboard.view',
                    'candidates.view', 'candidates.view_details', 'candidates.download_files',
                    'candidates.update_status', 'candidates.delete', 'candidates.export',
                    'ai_analysis.view', 'ai_analysis.trigger', 'ai_analysis.edit', 'ai_analysis.delete',
                    'job_positions.view', 'job_positions.create', 'job_positions.edit', 'job_positions.delete',
                    'users.view', 'users.create', 'users.edit', 'users.block',
                    'roles.view', 'roles.assign',
                    'reports.view', 'reports.export', 'reports.advanced',
                    'system.settings', 'system.logs'
                ],
                'is_active' => true,
            ],
            [
                'name' => 'hr_director',
                'display_name' => 'Directeur RH',
                'description' => 'Supervision complète du processus de recrutement',
                'permissions' => [
                    'dashboard.view',
                    'candidates.view', 'candidates.view_details', 'candidates.download_files',
                    'candidates.update_status', 'candidates.export',
                    'ai_analysis.view', 'ai_analysis.trigger', 'ai_analysis.edit',
                    'job_positions.view', 'job_positions.create', 'job_positions.edit', 'job_positions.delete',
                    'users.view', 'users.create', 'users.edit',
                    'reports.view', 'reports.export', 'reports.advanced'
                ],
                'is_active' => true,
            ],
            [
                'name' => 'hr_manager',
                'display_name' => 'Responsable RH',
                'description' => 'Gestion des candidatures et analyses IA',
                'permissions' => [
                    'dashboard.view',
                    'candidates.view', 'candidates.view_details', 'candidates.download_files',
                    'candidates.update_status', 'candidates.export',
                    'ai_analysis.view', 'ai_analysis.trigger', 'ai_analysis.edit',
                    'job_positions.view', 'job_positions.create', 'job_positions.edit',
                    'reports.view', 'reports.export'
                ],
                'is_active' => true,
            ],
            [
                'name' => 'senior_recruiter',
                'display_name' => 'Recruteur Senior',
                'description' => 'Gestion avancée des candidatures et analyses',
                'permissions' => [
                    'candidates.view', 'candidates.view_details', 'candidates.download_files',
                    'candidates.update_status', 'candidates.export',
                    'ai_analysis.view', 'ai_analysis.trigger',
                    'job_positions.view', 'job_positions.create', 'job_positions.edit',
                    'reports.view', 'reports.export'
                ],
                'is_active' => true,
            ],
            [
                'name' => 'recruiter',
                'display_name' => 'Recruteur',
                'description' => 'Consultation et évaluation des candidatures',
                'permissions' => [
                    'candidates.view', 'candidates.view_details', 'candidates.download_files',
                    'candidates.update_status',
                    'ai_analysis.view', 'ai_analysis.trigger',
                    'job_positions.view',
                    'reports.view'
                ],
                'is_active' => true,
            ],
            [
                'name' => 'junior_recruiter',
                'display_name' => 'Recruteur Junior',
                'description' => 'Consultation des candidatures avec supervision',
                'permissions' => [
                    'candidates.view', 'candidates.view_details', 'candidates.download_files',
                    'ai_analysis.view',
                    'job_positions.view',
                    'reports.view'
                ],
                'is_active' => true,
            ],
            [
                'name' => 'analyst',
                'display_name' => 'Analyste RH',
                'description' => 'Spécialisé dans l\'analyse des données et rapports',
                'permissions' => [
                    'candidates.view', 'candidates.view_details',
                    'ai_analysis.view',
                    'job_positions.view',
                    'reports.view', 'reports.export', 'reports.advanced'
                ],
                'is_active' => true,
            ],
            [
                'name' => 'viewer',
                'display_name' => 'Observateur',
                'description' => 'Accès en lecture seule aux candidatures',
                'permissions' => [
                    'candidates.view', 'candidates.view_details',
                    'ai_analysis.view',
                    'job_positions.view',
                    'reports.view'
                ],
                'is_active' => true,
            ],
        ];
    }
}
