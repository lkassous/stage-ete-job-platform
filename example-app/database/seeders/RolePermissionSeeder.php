<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();

        try {
            // 1. Créer toutes les permissions
            $this->createPermissions();

            // 2. Créer tous les rôles
            $this->createRoles();

            // 3. Assigner les permissions aux rôles
            $this->assignPermissionsToRoles();

            DB::commit();

            $this->command->info('✅ Rôles et permissions créés avec succès !');
            $this->displaySummary();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Erreur lors de la création des rôles et permissions : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Créer toutes les permissions
     */
    private function createPermissions(): void
    {
        $this->command->info('📝 Création des permissions...');

        $permissions = Permission::getDefaultPermissions();

        foreach ($permissions as $permissionData) {
            Permission::updateOrCreate(
                ['name' => $permissionData['name']],
                $permissionData
            );
        }

        $this->command->info('✅ ' . count($permissions) . ' permissions créées/mises à jour');
    }

    /**
     * Créer tous les rôles
     */
    private function createRoles(): void
    {
        $this->command->info('👥 Création des rôles...');

        $roles = Role::getDefaultRoles();

        foreach ($roles as $roleData) {
            // Extraire les permissions pour les traiter séparément
            $permissions = $roleData['permissions'] ?? [];
            unset($roleData['permissions']);

            Role::updateOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
        }

        $this->command->info('✅ ' . count($roles) . ' rôles créés/mis à jour');
    }

    /**
     * Assigner les permissions aux rôles
     */
    private function assignPermissionsToRoles(): void
    {
        $this->command->info('🔗 Attribution des permissions aux rôles...');

        $rolesData = Role::getDefaultRoles();

        foreach ($rolesData as $roleData) {
            $role = Role::where('name', $roleData['name'])->first();
            
            if (!$role) {
                continue;
            }

            $permissions = $roleData['permissions'] ?? [];
            $permissionIds = [];

            foreach ($permissions as $permissionName) {
                $permission = Permission::where('name', $permissionName)->first();
                if ($permission) {
                    $permissionIds[] = $permission->id;
                }
            }

            // Synchroniser les permissions pour ce rôle
            $role->permissions()->sync($permissionIds);

            $this->command->info("  ✅ {$role->display_name}: " . count($permissionIds) . " permissions");
        }
    }

    /**
     * Afficher un résumé des rôles et permissions créés
     */
    private function displaySummary(): void
    {
        $this->command->info("\n📊 RÉSUMÉ DU SYSTÈME DE RÔLES ET PERMISSIONS");
        $this->command->info("=" . str_repeat("=", 50));

        // Statistiques générales
        $totalPermissions = Permission::count();
        $totalRoles = Role::count();
        $activeRoles = Role::where('is_active', true)->count();

        $this->command->info("📈 Statistiques:");
        $this->command->info("  - Permissions totales: {$totalPermissions}");
        $this->command->info("  - Rôles totaux: {$totalRoles}");
        $this->command->info("  - Rôles actifs: {$activeRoles}");

        // Permissions par catégorie
        $this->command->info("\n📋 Permissions par catégorie:");
        $categories = Permission::getCategories();
        foreach ($categories as $category => $displayName) {
            $count = Permission::where('category', $category)->count();
            $this->command->info("  - {$displayName}: {$count} permissions");
        }

        // Rôles avec nombre de permissions
        $this->command->info("\n👥 Rôles et leurs permissions:");
        $roles = Role::with('permissions')->orderBy('name')->get();
        foreach ($roles as $role) {
            $permissionCount = $role->permissions()->count();
            $status = $role->is_active ? '✅' : '❌';
            $this->command->info("  {$status} {$role->display_name}: {$permissionCount} permissions");
        }

        $this->command->info("\n🎯 Système de rôles prêt pour le filtrage CV !");
        $this->command->info("   Vous pouvez maintenant assigner des rôles aux utilisateurs.");
    }
}
