<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier que l'utilisateur est authentifié
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié. Veuillez vous connecter.'
            ], 401);
        }

        $user = Auth::user();

        // Vérifier que l'utilisateur est un admin
        if ($user->user_type !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Accès refusé. Seuls les administrateurs peuvent accéder à cette ressource.'
            ], 403);
        }

        // Vérifier que l'utilisateur est actif
        if (!$user->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Votre compte administrateur a été suspendu.'
            ], 403);
        }

        return $next($request);
    }
}
