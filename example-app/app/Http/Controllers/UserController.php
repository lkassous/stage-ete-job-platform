<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Lister tous les utilisateurs avec leurs rôles
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = User::with(['role']);

            // Filtrer par rôle si spécifié
            if ($request->has('role') && $request->role) {
                $query->whereHas('role', function ($q) use ($request) {
                    $q->where('name', $request->role);
                });
            }

            // Pagination
            $perPage = $request->get('per_page', 10);
            $users = $query->orderBy('created_at', 'desc')->paginate($perPage);

            // Transformer les données pour l'affichage
            $usersData = $users->getCollection()->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'is_active' => $user->is_active ?? true,
                    'last_login_at' => $user->last_login_at,
                    'created_at' => $user->created_at,
                    'roles' => $user->role ? [
                        [
                            'id' => $user->role->id,
                            'name' => $user->role->name,
                            'display_name' => $user->role->display_name
                        ]
                    ] : []
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $usersData,
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                    'from' => $users->firstItem(),
                    'to' => $users->lastItem(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading users: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des utilisateurs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher un utilisateur spécifique
     */
    public function show($id): JsonResponse
    {
        try {
            $user = User::with(['role'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'is_active' => $user->is_active ?? true,
                    'last_login_at' => $user->last_login_at,
                    'created_at' => $user->created_at,
                    'role' => $user->role
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non trouvé',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Modifier le rôle d'un utilisateur
     */
    public function updateRole(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'role_id' => 'required|integer|exists:roles,id',
            'reason' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::findOrFail($id);

            // Enregistrer l'ancien rôle pour l'historique
            $oldRole = $user->role;

            // Mettre à jour le rôle
            $user->role_id = $request->role_id;
            $user->save();

            // Charger le nouveau rôle
            $user->load('role');

            return response()->json([
                'success' => true,
                'message' => 'Rôle mis à jour avec succès',
                'data' => [
                    'user_id' => $user->id,
                    'old_role' => $oldRole ? $oldRole->display_name : 'Aucun',
                    'new_role' => $user->role ? $user->role->display_name : 'Aucun',
                    'reason' => $request->reason
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la modification du rôle',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
