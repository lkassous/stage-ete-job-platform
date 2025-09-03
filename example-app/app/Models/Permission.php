<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'category',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all roles that have this permission.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions');
    }

    /**
     * Get permissions by category.
     */
    public static function getByCategory(string $category): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('category', $category)->where('is_active', true)->get();
    }

    /**
     * Default permissions for CV Filtering System.
     */
    public static function getDefaultPermissions(): array
    {
        return [
            // === GESTION DES CANDIDATURES ===
            [
                'name' => 'candidates.view',
                'display_name' => 'Voir les candidatures',
                'description' => 'Consulter la liste des candidatures',
                'category' => 'candidates',
                'is_active' => true,
            ],
            [
                'name' => 'candidates.view_details',
                'display_name' => 'Voir détails candidature',
                'description' => 'Consulter les détails complets d\'une candidature',
                'category' => 'candidates',
                'is_active' => true,
            ],
            [
                'name' => 'candidates.download_files',
                'display_name' => 'Télécharger CV/LM',
                'description' => 'Télécharger les CV et lettres de motivation',
                'category' => 'candidates',
                'is_active' => true,
            ],
            [
                'name' => 'candidates.update_status',
                'display_name' => 'Modifier statut candidature',
                'description' => 'Changer le statut d\'une candidature',
                'category' => 'candidates',
                'is_active' => true,
            ],
            [
                'name' => 'candidates.delete',
                'display_name' => 'Supprimer candidature',
                'description' => 'Supprimer définitivement une candidature',
                'category' => 'candidates',
                'is_active' => true,
            ],
            [
                'name' => 'candidates.export',
                'display_name' => 'Exporter candidatures',
                'description' => 'Exporter les données des candidatures',
                'category' => 'candidates',
                'is_active' => true,
            ],

            // === ANALYSE IA ===
            [
                'name' => 'ai_analysis.view',
                'display_name' => 'Voir analyses IA',
                'description' => 'Consulter les analyses IA des CV',
                'category' => 'ai_analysis',
                'is_active' => true,
            ],
            [
                'name' => 'ai_analysis.trigger',
                'display_name' => 'Lancer analyse IA',
                'description' => 'Déclencher une nouvelle analyse IA',
                'category' => 'ai_analysis',
                'is_active' => true,
            ],
            [
                'name' => 'ai_analysis.edit',
                'display_name' => 'Modifier analyse IA',
                'description' => 'Modifier ou corriger une analyse IA',
                'category' => 'ai_analysis',
                'is_active' => true,
            ],
            [
                'name' => 'ai_analysis.delete',
                'display_name' => 'Supprimer analyse IA',
                'description' => 'Supprimer une analyse IA',
                'category' => 'ai_analysis',
                'is_active' => true,
            ],

            // === GESTION DES POSTES ===
            [
                'name' => 'job_positions.view',
                'display_name' => 'Voir postes',
                'description' => 'Consulter les postes à pourvoir',
                'category' => 'job_positions',
                'is_active' => true,
            ],
            [
                'name' => 'job_positions.create',
                'display_name' => 'Créer poste',
                'description' => 'Créer un nouveau poste',
                'category' => 'job_positions',
                'is_active' => true,
            ],
            [
                'name' => 'job_positions.edit',
                'display_name' => 'Modifier poste',
                'description' => 'Modifier un poste existant',
                'category' => 'job_positions',
                'is_active' => true,
            ],
            [
                'name' => 'job_positions.delete',
                'display_name' => 'Supprimer poste',
                'description' => 'Supprimer un poste',
                'category' => 'job_positions',
                'is_active' => true,
            ],

            // === GESTION DES UTILISATEURS ===
            [
                'name' => 'users.view',
                'display_name' => 'Voir utilisateurs',
                'description' => 'Consulter la liste des utilisateurs',
                'category' => 'users',
                'is_active' => true,
            ],
            [
                'name' => 'users.create',
                'display_name' => 'Créer utilisateur',
                'description' => 'Créer un nouvel utilisateur',
                'category' => 'users',
                'is_active' => true,
            ],
            [
                'name' => 'users.edit',
                'display_name' => 'Modifier utilisateur',
                'description' => 'Modifier un utilisateur existant',
                'category' => 'users',
                'is_active' => true,
            ],
            [
                'name' => 'users.delete',
                'display_name' => 'Supprimer utilisateur',
                'description' => 'Supprimer un utilisateur',
                'category' => 'users',
                'is_active' => true,
            ],
            [
                'name' => 'users.block',
                'display_name' => 'Bloquer utilisateur',
                'description' => 'Bloquer/débloquer un utilisateur',
                'category' => 'users',
                'is_active' => true,
            ],

            // === GESTION DES RÔLES ===
            [
                'name' => 'roles.view',
                'display_name' => 'Voir rôles',
                'description' => 'Consulter les rôles et permissions',
                'category' => 'roles',
                'is_active' => true,
            ],
            [
                'name' => 'roles.create',
                'display_name' => 'Créer rôle',
                'description' => 'Créer un nouveau rôle',
                'category' => 'roles',
                'is_active' => true,
            ],
            [
                'name' => 'roles.edit',
                'display_name' => 'Modifier rôle',
                'description' => 'Modifier un rôle existant',
                'category' => 'roles',
                'is_active' => true,
            ],
            [
                'name' => 'roles.delete',
                'display_name' => 'Supprimer rôle',
                'description' => 'Supprimer un rôle',
                'category' => 'roles',
                'is_active' => true,
            ],
            [
                'name' => 'roles.assign',
                'display_name' => 'Assigner rôles',
                'description' => 'Assigner des rôles aux utilisateurs',
                'category' => 'roles',
                'is_active' => true,
            ],

            // === DASHBOARD ===
            [
                'name' => 'dashboard.view',
                'display_name' => 'Voir dashboard',
                'description' => 'Accéder au tableau de bord principal',
                'category' => 'dashboard',
                'is_active' => true,
            ],

            // === RAPPORTS ET STATISTIQUES ===
            [
                'name' => 'reports.view',
                'display_name' => 'Voir rapports',
                'description' => 'Consulter les rapports et statistiques',
                'category' => 'reports',
                'is_active' => true,
            ],
            [
                'name' => 'reports.export',
                'display_name' => 'Exporter rapports',
                'description' => 'Exporter les rapports',
                'category' => 'reports',
                'is_active' => true,
            ],
            [
                'name' => 'reports.advanced',
                'display_name' => 'Rapports avancés',
                'description' => 'Accès aux rapports avancés et analytics',
                'category' => 'reports',
                'is_active' => true,
            ],

            // === CONFIGURATION SYSTÈME ===
            [
                'name' => 'system.settings',
                'display_name' => 'Paramètres système',
                'description' => 'Modifier les paramètres du système',
                'category' => 'system',
                'is_active' => true,
            ],
            [
                'name' => 'system.ai_config',
                'display_name' => 'Configuration IA',
                'description' => 'Configurer les paramètres d\'IA',
                'category' => 'system',
                'is_active' => true,
            ],
            [
                'name' => 'system.logs',
                'display_name' => 'Voir logs système',
                'description' => 'Consulter les logs du système',
                'category' => 'system',
                'is_active' => true,
            ],
            [
                'name' => 'system.backup',
                'display_name' => 'Gestion sauvegardes',
                'description' => 'Créer et gérer les sauvegardes',
                'category' => 'system',
                'is_active' => true,
            ],

            // === PERMISSION SPÉCIALE ===
            [
                'name' => '*',
                'display_name' => 'Accès complet',
                'description' => 'Accès complet à toutes les fonctionnalités',
                'category' => 'system',
                'is_active' => true,
            ],
        ];
    }

    /**
     * Get permission categories.
     */
    public static function getCategories(): array
    {
        return [
            'dashboard' => 'Tableau de bord',
            'candidates' => 'Gestion des candidatures',
            'ai_analysis' => 'Analyse IA',
            'job_positions' => 'Postes à pourvoir',
            'users' => 'Gestion des utilisateurs',
            'roles' => 'Gestion des rôles',
            'reports' => 'Rapports et statistiques',
            'system' => 'Configuration système',
        ];
    }
}
