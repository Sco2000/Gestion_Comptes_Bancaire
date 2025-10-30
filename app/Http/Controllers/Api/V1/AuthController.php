<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\Token;

/**
 * @OA\Info(
 *     title="Gestion Comptes API",
 *     version="1.0.0",
 *     description="API pour la gestion des comptes bancaires"
 * )
 * @OA\Server(url="http://localhost:8000", description="Serveur local de développement")
 * @OA\Server(url="https://gestion-comptes-bancaire-marra-ousmane.onrender.com", description="Serveur de production")
 * @OA\Tag(
 *     name="Auth",
 *     description="Authentification et autorisation"
 * )
 * @OA\Tag(
 *     name="Comptes",
 *     description="Gestion des comptes bancaires"
 * )
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/auth/login",
     *     tags={"Auth"},
     *     summary="Connexion utilisateur",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="login", type="string", example="user@example.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Connexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="refresh_token", type="string"),
     *             @OA\Property(property="token_type", type="string", example="Bearer"),
     *             @OA\Property(property="expires_in", type="integer"),
     *             @OA\Property(property="role", type="string", example="admin"),
     *             @OA\Property(property="permissions", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Identifiants invalides")
     * )
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->login)
                    ->orWhere('login', $request->login)
                    ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['Les identifiants fournis sont incorrects.'],
            ]);
        }

        if (!$user->actif) {
            throw ValidationException::withMessages([
                'login' => ['Votre compte est désactivé.'],
            ]);
        }

        // Définir les scopes selon le rôle
        $scopes = $user->permissions;

        $token = $user->createToken('API Token', $scopes)->accessToken;

        // Créer un refresh token
        $refreshToken = $user->createToken('Refresh Token', ['*'])->token;
        $refreshToken->expires_at = now()->addDays(30);
        $refreshToken->save();

        $response = response()->json([
            'access_token' => $token,
            'refresh_token' => $refreshToken->id,
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'role' => $user->role,
            'permissions' => $user->permissions,
        ]);

        // Stocker le token dans les cookies
        $response->cookie('access_token', $token, 60, '/', null, false, true); // 1 heure
        $response->cookie('refresh_token', $refreshToken->id, 60*24*30, '/', null, false, true); // 30 jours

        return $response;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/refresh",
     *     tags={"Auth"},
     *     summary="Rafraîchir le token d'accès",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="refresh_token", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Token rafraîchi",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="token_type", type="string", example="Bearer"),
     *             @OA\Property(property="expires_in", type="integer"),
     *             @OA\Property(property="role", type="string", example="admin"),
     *             @OA\Property(property="permissions", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Token de rafraîchissement invalide")
     * )
     */
    public function refresh(Request $request)
    {
        $request->validate([
            'refresh_token' => 'required|string',
        ]);

        $refreshToken = Token::find($request->refresh_token);

        if (!$refreshToken || $refreshToken->revoked || $refreshToken->expires_at->isPast()) {
            return response()->json(['message' => 'Refresh token invalide'], 401);
        }

        $user = $refreshToken->user;

        // Révoquer l'ancien token d'accès
        $user->tokens()->where('id', '!=', $refreshToken->id)->delete();

        // Créer un nouveau token d'accès
        $newToken = $user->createToken('API Token')->accessToken;

        $response = response()->json([
            'access_token' => $newToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'role' => $user->role,
            'permissions' => $user->permissions,
        ]);

        // Mettre à jour le cookie
        $response->cookie('access_token', $newToken, 60, '/', null, false, true);

        return $response;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/logout",
     *     tags={"Auth"},
     *     summary="Déconnexion utilisateur",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Déconnexion réussie"
     *     ),
     *     @OA\Response(response=401, description="Non autorisé")
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        $response = response()->json(['message' => 'Déconnexion réussie']);

        // Supprimer les cookies
        $response->cookie('access_token', '', -1, '/', null, false, true);
        $response->cookie('refresh_token', '', -1, '/', null, false, true);

        return $response;
    }
}
