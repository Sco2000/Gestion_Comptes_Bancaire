<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use App\Enums\ErrorCode;
use App\Enums\HttpStatusCode;
use App\Exceptions\CustomApiException;

class ClientController extends Controller
{
    /**
     * @OA\Patch(
     *     path="/api/v1/clients/{compteId}",
     *     summary="Mettre à jour les informations d'un client via son compte",
     *     description="Met à jour partiellement les informations d'un client en utilisant l'ID d'un de ses comptes. Les modifications sont appliquées dans la base Render.",
     *     operationId="updateClientViaCompte",
     *     tags={"Clients"},
     *     @OA\Parameter(
     *         name="compteId",
     *         in="path",
     *         required=true,
     *         description="ID du compte appartenant au client",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="prenom", type="string", nullable=true, example="John"),
     *             @OA\Property(property="nom", type="string", nullable=true, example="Doe"),
     *             @OA\Property(property="email", type="string", format="email", nullable=true, example="john.doe@example.com"),
     *             @OA\Property(property="telephone", type="string", nullable=true, example="+221771234567"),
     *             @OA\Property(property="adresse", type="string", nullable=true, example="123 Main St"),
     *             @OA\Property(property="nci", type="string", nullable=true, example="1234567890123"),
     *             @OA\Property(property="date_naissance", type="string", format="date", nullable=true, example="1990-01-01")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Client mis à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="uuid"),
     *                 @OA\Property(property="prenom", type="string", nullable=true, example="John"),
     *                 @OA\Property(property="nom", type="string", example="Doe"),
     *                 @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *                 @OA\Property(property="telephone", type="string", example="+221771234567"),
     *                 @OA\Property(property="adresse", type="string", nullable=true, example="123 Main St"),
     *                 @OA\Property(property="nci", type="string", nullable=true, example="1234567890123"),
     *                 @OA\Property(property="date_naissance", type="string", format="date", nullable=true, example="1990-01-01")
     *             ),
     *             @OA\Property(property="message", type="string", example="Client mis à jour avec succès")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Client non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Client non trouvé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erreur de validation"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function update(Request $request, string $compteId)
    {
        try {
            // Récupérer le compte pour trouver le client
            $compte = \App\Models\Compte::find($compteId);

            if (!$compte) {
                throw new CustomApiException(
                    ErrorCode::COMPTE_NOT_FOUND,
                    HttpStatusCode::NOT_FOUND,
                    null,
                    ['compteId' => $compteId]
                );
            }

            // Récupérer le client propriétaire du compte
            $client = $compte->client;

            if (!$client) {
                throw new CustomApiException(
                    ErrorCode::CLIENT_NOT_FOUND,
                    HttpStatusCode::NOT_FOUND,
                    null,
                    ['compteId' => $compteId]
                );
            }

            // Valider les données d'entrée
            $validatedData = $request->validate([
                'prenom' => 'nullable|string|max:100',
                'nom' => 'nullable|string|max:100',
                'email' => 'nullable|email',
                'telephone' => ['nullable', new \App\Rules\ValidTelephone()],
                'adresse' => 'nullable|string|max:255',
                'nci' => 'nullable|string|max:50|unique:clients,nci,' . $clientId,
                'date_naissance' => 'nullable|date',
            ]);

            // Séparer les données client et utilisateur
            $clientData = array_intersect_key($validatedData, array_flip([
                'prenom', 'nom', 'adresse', 'nci', 'date_naissance'
            ]));

            $userData = array_intersect_key($validatedData, array_flip([
                'email', 'telephone'
            ]));

            // Mettre à jour le client
            if (!empty($clientData)) {
                $client->update($clientData);
            }

            // Mettre à jour l'utilisateur si nécessaire
            if (!empty($userData)) {
                $client->user->update($userData);
            }

            // Recharger le client avec les relations
            $client->load('user');

            return $this->successResponse($client, 'Client mis à jour avec succès');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw new CustomApiException(
                ErrorCode::VALIDATION_ERROR,
                HttpStatusCode::UNPROCESSABLE_ENTITY,
                'Erreur de validation',
                $e->errors()
            );
        } catch (\Throwable $e) {
            throw $e; // Let the middleware handle it
        }
    }
}
