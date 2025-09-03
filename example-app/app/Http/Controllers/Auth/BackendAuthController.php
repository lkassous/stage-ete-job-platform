<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class BackendAuthController extends Controller
{
    /**
     * Inscription d'un utilisateur backend (admin/RH/recruteur)
     */
    public function register(Request $request): JsonResponse
    {
        try {
            // Validation des données
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'phone' => 'nullable|string|max:20',
                'linkedin_url' => 'nullable|url|max:255',
                'role_name' => 'required|string|exists:roles,name',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreurs de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Récupérer le rôle
            $role = Role::where('name', $request->role_name)->first();
            if (!$role) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rôle invalide'
                ], 400);
            }

            // Créer l'utilisateur
            $user = User::create([
                'name' => $request->first_name . ' ' . $request->last_name,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'linkedin_url' => $request->linkedin_url,
                'role_id' => $role->id,
                'user_type' => $request->role_name,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            // Créer le token d'accès
            $tokenResult = $user->createToken('backend-token');
            $token = $tokenResult->accessToken;

            return response()->json([
                'success' => true,
                'message' => 'Inscription réussie',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'linkedin_url' => $user->linkedin_url,
                        'user_type' => $user->user_type,
                        'role' => [
                            'id' => $role->id,
                            'name' => $role->name,
                            'display_name' => $role->display_name,
                            'permissions' => $role->permissions,
                        ],
                        'is_active' => $user->is_active,
                        'created_at' => $user->created_at,
                    ],
                    'token' => $token,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'inscription',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les rôles disponibles pour l'inscription
     */
    public function getAvailableRoles(): JsonResponse
    {
        try {
            $roles = Role::where('is_active', true)
                ->select('name', 'display_name', 'description', 'is_active')
                ->orderBy('display_name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $roles
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
     * Connexion d'un utilisateur backend
     */
    public function login(Request $request): JsonResponse
    {
        try {
            // Validation des données
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreurs de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Tentative de connexion
            $credentials = $request->only('email', 'password');

            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Identifiants incorrects'
                ], 401);
            }

            $user = Auth::user();

            // Vérifier que l'utilisateur est actif
            if (!$user->isActive()) {
                Auth::logout();
                return response()->json([
                    'success' => false,
                    'message' => 'Votre compte a été suspendu. Contactez l\'administrateur.'
                ], 403);
            }

            // Charger le rôle pour vérifier les permissions
            $user->load('role');

            // Vérifier que l'utilisateur a un rôle autorisé pour le backend
            $allowedRoles = [
                'super_admin', 'admin', 'hr_director', 'hr_manager',
                'senior_recruiter', 'recruiter', 'junior_recruiter', 'analyst', 'viewer'
            ];

            if (!$user->role || !in_array($user->role->name, $allowedRoles)) {
                Auth::logout();
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé. Cette interface est réservée au personnel autorisé.'
                ], 403);
            }

            // Créer le token d'accès
            $tokenResult = $user->createToken('backend-token');
            $token = $tokenResult->accessToken;

            return response()->json([
                'success' => true,
                'message' => 'Connexion réussie',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'linkedin_url' => $user->linkedin_url,
                        'user_type' => $user->user_type,
                        'role' => $user->role ? [
                            'id' => $user->role->id,
                            'name' => $user->role->name,
                            'display_name' => $user->role->display_name,
                            'permissions' => $user->role->permissions,
                        ] : null,
                        'is_active' => $user->is_active,
                        'created_at' => $user->created_at,
                    ],
                    'token' => $token,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la connexion',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Déconnexion d'un utilisateur backend
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            if ($user) {
                // Révoquer tous les tokens de l'utilisateur
                $user->tokens()->delete();
            }

            return response()->json([
                'success' => true,
                'message' => 'Déconnexion réussie'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la déconnexion',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupération du profil utilisateur
     */
    public function profile(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $user->load('role');

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'linkedin_url' => $user->linkedin_url,
                        'user_type' => $user->user_type,
                        'role' => $user->role ? [
                            'id' => $user->role->id,
                            'name' => $user->role->name,
                            'display_name' => $user->role->display_name,
                            'permissions' => $user->role->permissions,
                        ] : null,
                        'is_active' => $user->is_active,
                        'blocked_at' => $user->blocked_at,
                        'blocked_reason' => $user->blocked_reason,
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du profil',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
