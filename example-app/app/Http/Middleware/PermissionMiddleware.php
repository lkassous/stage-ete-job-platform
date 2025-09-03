<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // Vérifier que l'utilisateur est authentifié
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié. Veuillez vous connecter.'
            ], 401);
        }

        $user = Auth::user();

        // Vérifier que l'utilisateur est actif
        if (!$user->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Votre compte a été suspendu. Contactez l\'administrateur.'
            ], 403);
        }

        // Vérifier que l'utilisateur a la permission requise
        if (!$user->hasPermission($permission)) {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé. Vous n\'avez pas les permissions nécessaires pour cette action.',
                'required_permission' => $permission,
                'user_permissions' => $user->role ? $user->role->permissions_array : []
            ], 403);
        }

        return $next($request);
    }
}
