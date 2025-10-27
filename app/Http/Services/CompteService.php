<?php

namespace App\Http\Services;

use App\Enums\ErrorCode;
use App\Enums\HttpStatusCode;
use App\Exceptions\CustomApiException;
use App\Http\Repositories\CompteRepository;
use App\Models\Client;
use App\Models\Compte;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CompteService
{
    protected $repository;

    public function __construct(CompteRepository $repository)
    {
        $this->repository = $repository;
    }

    public function listComptes(array $filters, $sort, $order, $limit, $user)
    {
        // Si c'est un client, filtrer seulement ses comptes
        if ($user && $user->isClient()) {
            $filters['client_id'] = $user->client->id;
        }

        return $this->repository->getAll($filters, $sort, $order, $limit, $user);
    }

    /**
     * Créer un nouveau compte bancaire avec client
     *
     * @param array $data
     * @return Compte
     */
    public function createCompte(array $data): Compte
    {
        return DB::transaction(function () use ($data) {
            // Vérifier si l'utilisateur existe
            $user = User::where('email', $data['client']['email'])
                       ->orWhere('telephone', $data['client']['telephone'])
                       ->first();

            if (!$user) {
                // Générer un mot de passe aléatoire
                $password = Str::random(8);

                // Créer l'utilisateur
                $user = User::create([
                    'nom' => $data['client']['nom'],
                    'email' => $data['client']['email'],
                    'password' => Hash::make($password),
                    'telephone' => $data['client']['telephone'],
                    'actif' => true,
                ]);

                // Créer le client associé
                $client = Client::create([
                    'user_id' => $user->id,
                    'adresse' => $data['client']['adresse'] ?? null,
                    'date_naissance' => $data['client']['date_naissance'] ?? null,
                ]);

                // TODO: Envoyer le mot de passe par email ou SMS
            } else {
                // Vérifier si l'utilisateur a déjà un client
                if (!$user->client) {
                    $client = Client::create([
                        'user_id' => $user->id,
                        'adresse' => $data['client']['adresse'] ?? null,
                        'date_naissance' => $data['client']['date_naissance'] ?? null,
                    ]);
                } else {
                    $client = $user->client;
                }
            }

            // Créer le compte
            $compte = $this->repository->create([
                'client_id' => $client->id,
                'numero_compte' => 'CPT-' . str_pad(mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT),
                'type' => $data['type'],
                'solde' => $data['solde'],
                'date_creation' => now(),
                'statut' => 'actif',
            ]);

            return $compte;
        });
    }

    /**
     * Récupérer un compte par ID
     *
     * @param string $id
     * @return Compte|null
     */
    public function getCompte(string $id): ?Compte
    {
        return $this->repository->findById($id);
    }

    /**
     * Récupérer un compte par ID en incluant les archivés
     *
     * @param string $id
     * @return Compte|null
     */
    public function getCompteWithArchived(string $id): ?Compte
    {
        return $this->repository->findByIdWithArchived($id);
    }

    /**
     * Mettre à jour un compte
     *
     * @param string $id
     * @param array $data
     * @return Compte
     */
    public function updateCompte(string $id, array $data): Compte
    {
        $compte = $this->getCompte($id);

        // Vérifier si le nouveau statut est autorisé pour ce type de compte
        if (isset($data['statut']) && !$compte->canChangeStatus($data['statut'])) {
            throw new CustomApiException(
                ErrorCode::COMPTE_STATUS_INVALID,
                HttpStatusCode::BAD_REQUEST,
                null,
                ['compteId' => $id, 'requestedStatus' => $data['statut']]
            );
        }

        return $this->repository->update($id, $data);
    }

    /**
     * Archiver un compte
     *
     * @param string $id
     * @return Compte
     */
    public function archiveCompte(string $id): Compte
    {
        return $this->repository->archive($id);
    }
}
