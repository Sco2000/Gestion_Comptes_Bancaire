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

    public function getAll(array $filters = [], $sort = 'created_at', $order = 'desc', $limit = 10, $user = null)
    {
        $query = $this->model->query();

        // if ($user && !$user->isAdmin()) {
        //     $query->where('client_id', $user->id);
        // }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['statut'])) {
            $query->where('statut', $filters['statut']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('numero_compte', 'like', "%$search%")
                  ->orWhereHas('client', function ($q2) use ($search) {
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
}
