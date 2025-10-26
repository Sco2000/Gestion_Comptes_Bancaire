<?php

namespace App\Http\Services;

use App\Http\Repositories\CompteRepository;
use App\Models\Client;
use App\Models\Compte;
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
            // Vérifier si le client existe
            $client = Client::where('email', $data['client']['email'])
                          ->orWhere('telephone', $data['client']['telephone'])
                          ->first();

            if (!$client) {
                // Créer le client
                $client = Client::create([
                    'id' => (string) Str::uuid(),
                    'prenom' => $data['client']['prenom'],
                    'nom' => $data['client']['nom'],
                    'email' => $data['client']['email'],
                    'telephone' => $data['client']['telephone'],
                    'adresse' => $data['client']['adresse'],
                    'nci' => $data['client']['nci'] ?? null,
                ]);

                // Générer un mot de passe aléatoire
                $password = Str::random(8);

                // Créer l'utilisateur associé
                $user = \App\Models\User::create([
                    'name' => $client->prenom . ' ' . $client->nom,
                    'email' => $client->email,
                    'password' => Hash::make($password),
                    'client_id' => $client->id,
                ]);

                // TODO: Envoyer le mot de passe par email ou SMS
            }

            // Créer le compte
            $compte = $this->repository->create([
                'client_id' => $client->id,
                'numero_compte' => 'CPT-' . str_pad(mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT),
                'type' => $data['type'],
                'solde' => $data['soldeInitial'],
                'statut' => 'actif',
            ]);

            return $compte;
        });
    }

    /**
     * Récupérer un compte par ID
     *
     * @param string $id
     * @return Compte
     * @throws CompteNotFoundException
     */
    public function getCompte(string $id): Compte
    {
        $compte = $this->repository->findById($id);

        if (!$compte) {
            throw new \App\Exceptions\CompteNotFoundException($id);
        }

        return $compte;
    }
}
