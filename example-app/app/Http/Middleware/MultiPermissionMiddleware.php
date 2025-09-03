<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class MultiPermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permissions  Permissions séparées par des pipes (|) pour OR ou des virgules (,) pour AND
     * @param  string  $operator  'or' ou 'and' (défaut: 'or')
     */
    public function handle(Request $request, Closure $next, string $permissions, string $operator = 'or'): Response
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

        // Parser les permissions
        $permissionList = [];
        if ($operator === 'or') {
            $permissionList = explode('|', $permissions);
        } else {
            $permissionList = explode(',', $permissions);
        }

        $permissionList = array_map('trim', $permissionList);

        // Vérifier les permissions selon l'opérateur
        $hasAccess = false;

        if ($operator === 'or') {
            // L'utilisateur doit avoir AU MOINS UNE des permissions
            foreach ($permissionList as $permission) {
                if ($user->hasPermission($permission)) {
                    $hasAccess = true;
                    break;
                }
            }
        } else {
            // L'utilisateur doit avoir TOUTES les permissions
            $hasAccess = true;
            foreach ($permissionList as $permission) {
                if (!$user->hasPermission($permission)) {
                    $hasAccess = false;
                    break;
                }
            }
        }

        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => $operator === 'or' 
                    ? 'Accès refusé. Vous devez avoir au moins une des permissions requises.'
                    : 'Accès refusé. Vous devez avoir toutes les permissions requises.',
                'required_permissions' => $permissionList,
                'operator' => $operator,
                'user_permissions' => $user->role ? $user->role->permissions_array : []
            ], 403);
        }

        return $next($request);
    }
}
