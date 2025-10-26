<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Compte;
use Illuminate\Http\Request;
use App\Http\Services\CompteService;
use App\Http\Resources\CompteResource;
use App\Http\Requests\CompteRequest;


/**
 * @OA\Info(
 *     title="Gestion Comptes API",
 *     version="1.0.0",
 *     description="API pour la gestion des comptes bancaires"
 * )
 */

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

        $filters = $request->only(['type', 'statut', 'search']);
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');
        $limit = min($request->get('limit', 10), 100);
        $query = Compte::query();

        $user = $request->user();

        $comptes = $this->compteService->listComptes($filters, $sort, $order, $limit, $user);
        // Liaison via le conteneur (pas de new / pas de static)
        $compteCollection = app('compte.resource.collection', ['collection' => $comptes]);

        return $this->successResponse($compteCollection, 'Liste des comptes');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CompteRequest $request)
    {
        $data = $request->validated();
        $compte = $this->compteService->createCompte($data);

        $compteResource = app(CompteResource::class, ['compte' => $compte]);

        return $this->successResponse($compteResource, 'Compte créé avec succès', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $compteId)
    {
        $compte = $this->compteService->getCompte($compteId);
        $compteResource = app(CompteResource::class, ['compte' => $compte]);
        return $this->successResponse($compteResource, 'Détails du compte');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
