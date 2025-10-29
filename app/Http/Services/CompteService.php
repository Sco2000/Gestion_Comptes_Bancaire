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
            // Vérifier si le client existe déjà via son CNI
            $client = null;
            if (isset($data['client']['nci'])) {
                $client = Client::where('nci', $data['client']['nci'])->first();
            }

            if ($client) {
                // Client existe déjà, vérifier qu'il n'a pas déjà ce type de compte
                $existingCompte = Compte::where('client_id', $client->id)
                    ->where('type', $data['type'])
                    ->first();

                if ($existingCompte) {
                    throw new CustomApiException(
                        ErrorCode::COMPTE_ALREADY_EXISTS,
                        HttpStatusCode::CONFLICT,
                        'Ce client possède déjà un compte de ce type.',
                        ['client_id' => $client->id, 'type' => $data['type']]
                    );
                }
            } else {
                // Créer le client et l'utilisateur
                $client = $this->createClient($data['client']);
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
     * Créer un client avec son utilisateur
     *
     * @param array $clientData
     * @return Client
     */
    private function createClient(array $clientData): Client
    {
        // Vérifier si l'utilisateur existe déjà
        $user = User::where('email', $clientData['email'])
                    ->orWhere('telephone', $clientData['telephone'])
                    ->first();

        if (!$user) {
            // Générer un mot de passe aléatoire
            $password = Str::random(8);

            // Créer l'utilisateur
            $user = User::create([
                'nom' => $clientData['nom'],
                'email' => $clientData['email'],
                'password' => Hash::make($password),
                'telephone' => $clientData['telephone'],
                'actif' => true,
            ]);

            // TODO: Envoyer le mot de passe par email ou SMS
        }

        // Créer le client associé
        $client = Client::create([
            'user_id' => $user->id,
            'prenom' => $clientData['prenom'] ?? null,
            'nom' => $clientData['nom'],
            'email' => $clientData['email'],
            'telephone' => $clientData['telephone'],
            'adresse' => $clientData['adresse'] ?? null,
            'nci' => $clientData['nci'] ?? null,
            'date_naissance' => $clientData['date_naissance'] ?? null,
        ]);

        return $client;
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
     * Valider les dates de blocage
     *
     * @param array $data
     * @throws CustomApiException
     */
    public function validateBlockingDates(array $data): void
    {
        if (isset($data['statut']) && $data['statut'] === 'bloque') {
            $now = now()->toDateString();

            // Vérifier que date_debut_blocage n'est pas dans le passé
            if (isset($data['date_debut_blocage']) && $data['date_debut_blocage'] < $now) {
                throw new CustomApiException(
                    ErrorCode::VALIDATION_ERROR,
                    HttpStatusCode::BAD_REQUEST,
                    'La date de début de blocage ne peut pas être antérieure à la date actuelle.',
                    ['date_debut_blocage' => $data['date_debut_blocage']]
                );
            }

            // Vérifier que date_fin_blocage n'est pas antérieure à date_debut_blocage
            if (isset($data['date_debut_blocage']) && isset($data['date_fin_blocage']) &&
                $data['date_fin_blocage'] < $data['date_debut_blocage']) {
                throw new CustomApiException(
                    ErrorCode::VALIDATION_ERROR,
                    HttpStatusCode::BAD_REQUEST,
                    'La date de fin de blocage ne peut pas être antérieure à la date de début.',
                    ['date_debut_blocage' => $data['date_debut_blocage'], 'date_fin_blocage' => $data['date_fin_blocage']]
                );
            }
        }
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

        // Si non trouvé, essayer avec les archivés
        if (!$compte) {
            $compte = $this->getCompteWithArchived($id);
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
