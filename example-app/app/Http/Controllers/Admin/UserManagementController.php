<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserManagementController extends Controller
{
    /**
     * Middleware pour vérifier que l'utilisateur est admin
     */
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            if (!$user || $user->user_type !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé. Seuls les administrateurs peuvent accéder à cette ressource.'
                ], 403);
            }
            return $next($request);
        });
    }

    /**
     * Lister tous les utilisateurs avec pagination et filtres
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = User::query();

            // Filtres
            if ($request->filled('user_type')) {
                $query->where('user_type', $request->user_type);
            }

            if ($request->filled('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'ILIKE', "%{$search}%")
                      ->orWhere('email', 'ILIKE', "%{$search}%")
                      ->orWhere('first_name', 'ILIKE', "%{$search}%")
                      ->orWhere('last_name', 'ILIKE', "%{$search}%");
                });
            }

            // Tri
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $users = $query->with(['candidateApplications', 'blockedBy'])->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'users' => $users->items(),
                    'pagination' => [
                        'current_page' => $users->currentPage(),
                        'last_page' => $users->lastPage(),
                        'per_page' => $users->perPage(),
                        'total' => $users->total(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des utilisateurs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher les détails d'un utilisateur
     */
    public function show(User $user): JsonResponse
    {
        try {
            $user->load(['candidateApplications', 'blockedBy', 'blockedUsers']);

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user,
                    'statistics' => [
                        'total_applications' => $user->candidateApplications->count(),
                        'pending_applications' => $user->candidateApplications->where('status', 'pending')->count(),
                        'analyzed_applications' => $user->candidateApplications->where('status', 'analyzed')->count(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de l\'utilisateur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bloquer un utilisateur
     */
    public function blockUser(Request $request, User $user): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'reason' => 'required|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreurs de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Vérifier qu'on ne bloque pas un admin
            if ($user->user_type === 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de bloquer un administrateur'
                ], 400);
            }

            // Vérifier que l'utilisateur n'est pas déjà bloqué
            if (!$user->isActive()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cet utilisateur est déjà bloqué'
                ], 400);
            }

            $admin = Auth::user();
            $user->block($request->reason, $admin);

            return response()->json([
                'success' => true,
                'message' => 'Utilisateur bloqué avec succès',
                'data' => [
                    'user' => $user->fresh()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du blocage de l\'utilisateur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Débloquer un utilisateur
     */
    public function unblockUser(User $user): JsonResponse
    {
        try {
            // Vérifier que l'utilisateur est bloqué
            if ($user->isActive()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cet utilisateur n\'est pas bloqué'
                ], 400);
            }

            $user->unblock();

            return response()->json([
                'success' => true,
                'message' => 'Utilisateur débloqué avec succès',
                'data' => [
                    'user' => $user->fresh()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du déblocage de l\'utilisateur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un utilisateur (soft delete)
     */
    public function destroy(User $user): JsonResponse
    {
        try {
            // Vérifier qu'on ne supprime pas un admin
            if ($user->user_type === 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer un administrateur'
                ], 400);
            }

            // Vérifier qu'on ne se supprime pas soi-même
            if ($user->id === Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez pas supprimer votre propre compte'
                ], 400);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Utilisateur supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'utilisateur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques des utilisateurs
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_users' => User::count(),
                'total_candidates' => User::candidates()->count(),
                'total_admins' => User::admins()->count(),
                'active_users' => User::active()->count(),
                'blocked_users' => User::blocked()->count(),
                'recent_registrations' => User::where('created_at', '>=', now()->subDays(7))->count(),
                'users_by_month' => User::selectRaw('DATE_TRUNC(\'month\', created_at) as month, COUNT(*) as count')
                    ->where('created_at', '>=', now()->subMonths(12))
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recherche avancée d'utilisateurs
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'query' => 'required|string|min:2',
                'filters' => 'sometimes|array',
                'filters.user_type' => 'sometimes|in:admin,candidate',
                'filters.is_active' => 'sometimes|boolean',
                'filters.date_from' => 'sometimes|date',
                'filters.date_to' => 'sometimes|date|after_or_equal:filters.date_from',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreurs de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            $query = User::query();
            $searchTerm = $request->query;

            // Recherche textuelle
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'ILIKE', "%{$searchTerm}%")
                  ->orWhere('email', 'ILIKE', "%{$searchTerm}%")
                  ->orWhere('first_name', 'ILIKE', "%{$searchTerm}%")
                  ->orWhere('last_name', 'ILIKE', "%{$searchTerm}%")
                  ->orWhere('phone', 'ILIKE', "%{$searchTerm}%");
            });

            // Appliquer les filtres
            if ($request->has('filters')) {
                $filters = $request->filters;

                if (isset($filters['user_type'])) {
                    $query->where('user_type', $filters['user_type']);
                }

                if (isset($filters['is_active'])) {
                    $query->where('is_active', $filters['is_active']);
                }

                if (isset($filters['date_from'])) {
                    $query->where('created_at', '>=', $filters['date_from']);
                }

                if (isset($filters['date_to'])) {
                    $query->where('created_at', '<=', $filters['date_to']);
                }
            }

            $users = $query->with(['candidateApplications'])
                          ->orderBy('created_at', 'desc')
                          ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => [
                    'users' => $users->items(),
                    'pagination' => [
                        'current_page' => $users->currentPage(),
                        'last_page' => $users->lastPage(),
                        'per_page' => $users->perPage(),
                        'total' => $users->total(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la recherche',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
