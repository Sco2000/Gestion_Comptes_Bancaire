<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Compte;
use Illuminate\Http\Request;
use App\Http\Services\CompteService;
use App\Http\Resources\CompteResource;
use App\Http\Requests\CompteRequest;
use App\Enums\ErrorCode;
use App\Enums\HttpStatusCode;
use App\Exceptions\CustomApiException;



class CompteController extends Controller
{

    public function __construct(CompteService $compteService)
    {
        $this->compteService = $compteService;
    }
    /**
     * @OA\Get(
     *     path="/api/v1/comptes",
     *     summary="Lister les comptes bancaires",
     *     description="Récupère la liste des comptes bancaires avec possibilité de filtrage, tri et pagination",
     *     operationId="getComptes",
     *     tags={"Comptes"},
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Type de compte (epargne, cheque, etc.)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"epargne", "cheque"})
     *     ),
     *     @OA\Parameter(
     *         name="statut",
     *         in="query",
     *         description="Statut du compte (actif, bloque)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"actif", "bloque"})
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Recherche par numéro de compte ou nom du titulaire",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Champ de tri",
     *         required=false,
     *         @OA\Schema(type="string", default="created_at")
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="query",
     *         description="Ordre de tri",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"}, default="desc")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Nombre d'éléments par page (max 100)",
     *         required=false,
     *         @OA\Schema(type="integer", default=10, maximum=100)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des comptes récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="numeroCompte", type="string", example="C00123456"),
     *                 @OA\Property(property="titulaire", type="string", example="Amadou Diallo"),
     *                 @OA\Property(property="type", type="string", enum={"epargne", "cheque"}, example="epargne"),
     *                 @OA\Property(property="solde", type="number", format="float", example=1250000),
     *                 @OA\Property(property="devise", type="string", example="FCFA"),
     *                 @OA\Property(property="dateCreation", type="string", format="date-time", example="2023-03-15T00:00:00Z"),
     *                 @OA\Property(property="statut", type="string", enum={"actif", "bloque"}, example="bloque"),
     *                 @OA\Property(property="motifBlocage", type="string", nullable=true, example="Inactivité de 30+ jours"),
     *                 @OA\Property(property="metadata", type="object", nullable=true,
     *                     @OA\Property(property="derniereModification", type="string", format="date-time", example="2023-06-10T14:30:00Z"),
     *                     @OA\Property(property="version", type="integer", example=1)
     *                 )
     *             )),
     *             @OA\Property(property="pagination", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="total", type="integer", example=50),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="to", type="integer", example=10),
     *                 @OA\Property(property="links", type="object",
     *                     @OA\Property(property="first", type="string", example="http://api.banque.example.com/api/v1/comptes?page=1"),
     *                     @OA\Property(property="last", type="string", example="http://api.banque.example.com/api/v1/comptes?page=5"),
     *                     @OA\Property(property="prev", type="string", nullable=true, example=null),
     *                     @OA\Property(property="next", type="string", example="http://api.banque.example.com/api/v1/comptes?page=2")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Liste des comptes")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé"
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->only(['type', 'statut', 'search']);
            $sort = $request->get('sort', 'created_at');
            $order = $request->get('order', 'desc');
            $limit = min($request->get('limit', 10), 100);

            $comptes = $this->compteService->listComptes($filters, $sort, $order, $limit, null);
            // Liaison via le conteneur (pas de new / pas de static)
            $compteCollection = app('compte.resource.collection', ['collection' => $comptes]);

            return $this->successResponse($compteCollection, 'Liste des comptes');
        } catch (\Throwable $e) {
            throw $e; // Let the middleware handle it
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/comptes",
     *     summary="Créer un nouveau compte bancaire",
     *     description="Crée un nouveau compte bancaire avec un client associé",
     *     operationId="createCompte",
     *     tags={"Comptes"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"type","solde","client"},
     *             @OA\Property(property="type", type="string", enum={"cheque","epargne"}, example="cheque"),
     *             @OA\Property(property="solde", type="number", minimum=10000, example=50000),
     *             @OA\Property(property="client", type="object", required={"prenom","nom","email","telephone"},
     *                 @OA\Property(property="prenom", type="string", example="John"),
     *                 @OA\Property(property="nom", type="string", example="Doe"),
     *                 @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *                 @OA\Property(property="telephone", type="string", example="+221771234567")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Compte créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="uuid"),
     *                 @OA\Property(property="numeroCompte", type="string", example="CPT-123456"),
     *                 @OA\Property(property="titulaire", type="string", example="John Doe"),
     *                 @OA\Property(property="type", type="string", example="cheque"),
     *                 @OA\Property(property="solde", type="number", example=50000),
     *                 @OA\Property(property="statut", type="string", example="actif")
     *             ),
     *             @OA\Property(property="message", type="string", example="Compte créé avec succès")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès refusé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Accès refusé")
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
    public function store(CompteRequest $request)
    {
        try {
            $data = $request->validated();
            $compte = $this->compteService->createCompte($data);

            $compteResource = app(CompteResource::class, ['compte' => $compte]);

            return $this->successResponse($compteResource, 'Compte créé avec succès', 201);
        } catch (\Throwable $e) {
            throw $e; // Let the middleware handle it
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/comptes/{compteId}",
     *     summary="Obtenir les détails d'un compte",
     *     description="Récupère les informations détaillées d'un compte bancaire spécifique",
     *     operationId="getCompte",
     *     tags={"Comptes"},
     *     @OA\Parameter(
     *         name="compteId",
     *         in="path",
     *         required=true,
     *         description="ID du compte bancaire",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails du compte récupérés",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="uuid"),
     *                 @OA\Property(property="numeroCompte", type="string", example="CPT-123456"),
     *                 @OA\Property(property="titulaire", type="string", example="John Doe"),
     *                 @OA\Property(property="type", type="string", example="cheque"),
     *                 @OA\Property(property="solde", type="number", example=50000),
     *                 @OA\Property(property="statut", type="string", example="actif")
     *             ),
     *             @OA\Property(property="message", type="string", example="Détails du compte")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès refusé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Accès refusé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Compte non trouvé")
     *         )
     *     )
     * )
     */
    public function show(Request $request, string $compteId)
    {
        try {
            // Essayer d'abord de récupérer le compte non archivé
            $compte = $this->compteService->getCompte($compteId);

            // Si non trouvé, essayer avec les archivés
            if (!$compte) {
                $compte = $this->compteService->getCompteWithArchived($compteId);
            }

            // Si toujours pas trouvé, retourner une erreur 404
            if (!$compte) {
                throw new CustomApiException(
                    ErrorCode::COMPTE_NOT_FOUND,
                    HttpStatusCode::NOT_FOUND,
                    null,
                    ['compteId' => $compteId]
                );
            }

            $compteResource = app(CompteResource::class, ['compte' => $compte]);
            return $this->successResponse($compteResource, 'Détails du compte');
        } catch (\Throwable $e) {
            throw $e; // Let the middleware handle it
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/comptes/{id}",
     *     summary="Modifier un compte bancaire",
     *     description="Modifie le statut d'un compte bancaire existant",
     *     operationId="updateCompte",
     *     tags={"Comptes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du compte bancaire",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"statut"},
     *             @OA\Property(property="statut", type="string", enum={"actif", "bloque"}, example="bloque")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte modifié avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="uuid"),
     *                 @OA\Property(property="numeroCompte", type="string", example="CPT-123456"),
     *                 @OA\Property(property="titulaire", type="string", example="John Doe"),
     *                 @OA\Property(property="type", type="string", example="cheque"),
     *                 @OA\Property(property="solde", type="number", example=50000),
     *                 @OA\Property(property="statut", type="string", example="bloque")
     *             ),
     *             @OA\Property(property="message", type="string", example="Compte modifié avec succès")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Statut non autorisé pour ce type de compte",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Statut non autorisé pour ce type de compte")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Compte non trouvé")
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
    public function update(Request $request, string $id)
    {
        // Essayer d'abord de récupérer le compte non archivé
        $compte = $this->compteService->getCompte($id);

        // Si non trouvé, essayer avec les archivés
        if (!$compte) {
            $compte = $this->compteService->getCompteWithArchived($id);
        }

        // Si toujours pas trouvé, retourner une erreur 404
        if (!$compte) {
            throw new CustomApiException(
                ErrorCode::COMPTE_NOT_FOUND,
                HttpStatusCode::NOT_FOUND,
                null,
                ['compteId' => $id]
            );
        }

        $data = $request->only(['statut']);

        // Vérifier si le nouveau statut est autorisé pour ce type de compte
        if (isset($data['statut']) && !$compte->canChangeStatus($data['statut'])) {
            throw new CustomApiException(
                ErrorCode::COMPTE_STATUS_INVALID,
                HttpStatusCode::BAD_REQUEST,
                null,
                ['compteId' => $id, 'requestedStatus' => $data['statut']]
            );
        }

        $compte->update($data);

        $compteResource = app(CompteResource::class, ['compte' => $compte]);
        return $this->successResponse($compteResource, 'Compte modifié avec succès');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/comptes/{id}",
     *     summary="Archiver un compte bancaire",
     *     description="Archive un compte bancaire existant (soft delete)",
     *     operationId="deleteCompte",
     *     tags={"Comptes"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du compte bancaire",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Compte archivé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Compte archivé avec succès")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Compte non trouvé")
     *         )
     *     )
     * )
     */
    public function destroy(Request $request, string $id)
    {
        try {
            $this->compteService->archiveCompte($id);

            return $this->successResponse(null, 'Compte archivé avec succès');
        } catch (\Throwable $e) {
            throw $e; // Let the middleware handle it
        }
    }
}
