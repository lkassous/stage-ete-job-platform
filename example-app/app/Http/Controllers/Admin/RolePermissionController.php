<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RolePermissionController extends Controller
{
    /**
     * Constructor - Middleware pour vérifier les permissions
     */
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('permission:roles.view')->only(['index', 'show', 'getPermissions']);
        $this->middleware('permission:roles.create')->only(['store']);
        $this->middleware('permission:roles.edit')->only(['update']);
        $this->middleware('permission:roles.delete')->only(['destroy']);
        $this->middleware('permission:roles.assign')->only(['assignRole', 'revokeRole']);
    }

    /**
     * Liste tous les rôles avec leurs permissions
     */
    public function index(): JsonResponse
    {
        try {
            $roles = Role::with('permissions')
                ->withCount('users')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'display_name' => $role->display_name,
                        'description' => $role->description,
                        'is_active' => $role->is_active,
                        'users_count' => $role->users_count,
                        'permissions' => $role->permissions->map(function ($permission) {
                            return [
                                'id' => $permission->id,
                                'name' => $permission->name,
                                'display_name' => $permission->display_name,
                                'category' => $permission->category,
                            ];
                        }),
                        'created_at' => $role->created_at,
                        'updated_at' => $role->updated_at,
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des rôles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Affiche un rôle spécifique
     */
    public function show(Role $role): JsonResponse
    {
        try {
            $role->load(['permissions', 'users']);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'display_name' => $role->display_name,
                    'description' => $role->description,
                    'is_active' => $role->is_active,
                    'permissions' => $role->permissions,
                    'users' => $role->users->map(function ($user) {
                        return [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'is_active' => $user->is_active,
                        ];
                    }),
                    'created_at' => $role->created_at,
                    'updated_at' => $role->updated_at,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du rôle',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crée un nouveau rôle
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:roles,name',
                'display_name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'permissions' => 'array',
                'permissions.*' => 'exists:permissions,id',
                'is_active' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $role = Role::create([
                'name' => $request->name,
                'display_name' => $request->display_name,
                'description' => $request->description,
                'is_active' => $request->get('is_active', true),
            ]);

            // Assigner les permissions
            if ($request->has('permissions')) {
                $role->permissions()->sync($request->permissions);
            }

            DB::commit();

            $role->load('permissions');

            return response()->json([
                'success' => true,
                'message' => 'Rôle créé avec succès',
                'data' => $role
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du rôle',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Met à jour un rôle
     */
    public function update(Request $request, Role $role): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
                'display_name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'permissions' => 'array',
                'permissions.*' => 'exists:permissions,id',
                'is_active' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $role->update([
                'name' => $request->name,
                'display_name' => $request->display_name,
                'description' => $request->description,
                'is_active' => $request->get('is_active', $role->is_active),
            ]);

            // Synchroniser les permissions
            if ($request->has('permissions')) {
                $role->permissions()->sync($request->permissions);
            }

            DB::commit();

            $role->load('permissions');

            return response()->json([
                'success' => true,
                'message' => 'Rôle mis à jour avec succès',
                'data' => $role
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du rôle',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprime un rôle
     */
    public function destroy(Role $role): JsonResponse
    {
        try {
            // Vérifier que le rôle n'est pas assigné à des utilisateurs
            if ($role->users()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer ce rôle car il est assigné à des utilisateurs'
                ], 422);
            }

            // Vérifier que ce n'est pas un rôle système critique
            $systemRoles = ['super_admin', 'admin'];
            if (in_array($role->name, $systemRoles)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer un rôle système'
                ], 422);
            }

            $role->delete();

            return response()->json([
                'success' => true,
                'message' => 'Rôle supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du rôle',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Liste toutes les permissions disponibles
     */
    public function getPermissions(): JsonResponse
    {
        try {
            $permissions = Permission::where('is_active', true)
                ->orderBy('category')
                ->orderBy('display_name')
                ->get();

            $groupedPermissions = $permissions->groupBy('category');

            return response()->json([
                'success' => true,
                'data' => [
                    'permissions' => $permissions,
                    'grouped_permissions' => $groupedPermissions,
                    'categories' => Permission::getCategories()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des permissions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assigne un rôle à un utilisateur
     */
    public function assignRole(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'role_id' => 'required|exists:roles,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::findOrFail($request->user_id);
            $role = Role::findOrFail($request->role_id);

            $user->update(['role_id' => $role->id]);

            return response()->json([
                'success' => true,
                'message' => "Rôle '{$role->display_name}' assigné à {$user->name} avec succès"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'assignation du rôle',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Révoque le rôle d'un utilisateur
     */
    public function revokeRole(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::findOrFail($request->user_id);
            $user->update(['role_id' => null]);

            return response()->json([
                'success' => true,
                'message' => "Rôle révoqué pour {$user->name} avec succès"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la révocation du rôle',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
