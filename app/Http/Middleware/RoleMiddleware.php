<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json(['message' => 'Non autorisé'], 401);
        }

        // Vérifier le rôle de l'utilisateur
        $userRole = $user->isAdmin() ? 'admin' : 'client';

        if ($userRole !== $role) {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        // Ajouter les permissions aux claims du token
        $permissions = $this->getUserPermissions($user);
        $request->merge(['permissions' => $permissions]);

        return $next($request);
    }

    /**
     * Récupérer les permissions de l'utilisateur
     */
    private function getUserPermissions($user): array
    {
        $permissions = [];

        if ($user->isAdmin()) {
            // Permissions pour les admins
            $permissions = [
                'comptes.read',
                'comptes.write',
                'comptes.delete',
                'clients.read',
                'clients.write',
                'users.read',
                'users.write',
            ];
        } elseif ($user->isClient()) {
            // Permissions pour les clients
            $permissions = [
                'comptes.read',
                'comptes.write', // Peut modifier ses propres comptes
            ];
        }

        return $permissions;
    }
}