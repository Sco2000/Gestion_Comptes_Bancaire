<?php

namespace App\Http\Repositories;

use App\Models\Compte;

class CompteRepository
{
    protected $model;

    public function __construct(Compte $compte)
    {
        $this->model = $compte;
    }

    public function getAll(array $filters = [], $sort = 'created_at', $order = 'desc', $limit = 10)
    {
        $query = $this->model->query();

        if (!empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['statut'])) {
            $query->withoutGlobalScopes();
            if ($filters['statut'] === 'archive') {
                $query->where('statut', 'supprimé');
            } else {
                $query->where('statut', $filters['statut']);
            }
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('numero_compte', 'like', "%$search%")
                  ->orWhereHas('client.user', function ($q2) use ($search) {
                      $q2->where('prenom', 'like', "%$search%")
                         ->orWhere('nom', 'like', "%$search%");
                  });
            });
        }

        return $query->orderBy($sort, $order)->paginate($limit);
    }

    /**
     * Créer un nouveau compte
     *
     * @param array $data
     * @return Compte
     */
    public function create(array $data): Compte
    {
        return $this->model->create($data);
    }

    /**
     * Trouver un compte par ID
     *
     * @param string $id
     * @return Compte|null
     */
    public function findById(string $id): ?Compte
    {
        return $this->model->find($id);
    }

    /**
     * Trouver un compte par ID en incluant les archivés
     *
     * @param string $id
     * @return Compte|null
     */
    public function findByIdWithArchived(string $id): ?Compte
    {
        return $this->model->withoutGlobalScope(\App\Models\Scopes\NonArchiveScope::class)->find($id);
    }

    /**
     * Mettre à jour un compte
     *
     * @param string $id
     * @param array $data
     * @return Compte
     */
    public function update(string $id, array $data): Compte
    {
        $compte = $this->findById($id);

        // Si non trouvé, essayer avec les archivés
        if (!$compte) {
            $compte = $this->findByIdWithArchived($id);
        }

        if ($compte) {
            $compte->update($data);
        }
        return $compte;
    }

    /**
     * Trouver un compte par numéro de compte
     *
     * @param string $numeroCompte
     * @return Compte|null
     */
    public function findByNumeroCompte(string $numeroCompte): ?Compte
    {
        return $this->model->where('numero_compte', $numeroCompte)->first();
    }

    /**
     * Trouver un compte par numéro de compte en incluant les archivés
     *
     * @param string $numeroCompte
     * @return Compte|null
     */
    public function findByNumeroCompteWithArchived(string $numeroCompte): ?Compte
    {
        return $this->model->withoutGlobalScope(\App\Models\Scopes\NonArchiveScope::class)
                          ->where('numero_compte', $numeroCompte)
                          ->first();
    }

    /**
     * Archiver un compte
     *
     * @param string $id
     * @return Compte
     */
    public function archive(string $id): Compte
    {
        $compte = $this->findById($id);
        if ($compte) {
            $compte->update(['statut' => 'supprimé']);
        }
        return $compte;
    }
}
