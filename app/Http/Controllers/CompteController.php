<?php

namespace App\Http\Controllers;

use App\Models\Compte;
use Illuminate\Http\Request;

/**
 * @OA\Info(
 *     title="Gestion Comptes API",
 *     version="1.0.0",
 *     description="API pour la gestion des comptes bancaires"
 * )
 */

class CompteController extends Controller
{
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
        $query = Compte::query();

        // Filtrage par type
        if ($request->has('type') && in_array($request->type, ['epargne', 'cheque'])) {
            $query->where('type', $request->type);
        }

        // Filtrage par statut
        if ($request->has('statut') && in_array($request->statut, ['actif', 'bloque'])) {
            $query->where('statut', $request->statut);
        }

        // Recherche par numéro de compte ou nom du titulaire
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('numero_compte', 'like', "%{$search}%")
                  ->orWhere('titulaire', 'like', "%{$search}%");
            });
        }

        // Tri
        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');
        $query->orderBy($sort, $order);

        // Pagination
        $limit = min($request->get('limit', 10), 100);
        $comptes = $query->paginate($limit);

        return response()->json([
            'success' => true,
            'data' => $comptes->items(),
            'pagination' => [
                'current_page' => $comptes->currentPage(),
                'per_page' => $comptes->perPage(),
                'total' => $comptes->total(),
                'last_page' => $comptes->lastPage(),
                'from' => $comptes->firstItem(),
                'to' => $comptes->lastItem(),
                'links' => [
                    'first' => $comptes->url(1),
                    'last' => $comptes->url($comptes->lastPage()),
                    'prev' => $comptes->previousPageUrl(),
                    'next' => $comptes->nextPageUrl(),
                ],
            ],
            'message' => 'Liste des comptes',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
