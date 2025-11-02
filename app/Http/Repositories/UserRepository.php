<?php

namespace App\Http\Repositories;

use App\Models\User;

class UserRepository
{
    protected $model;

    public function __construct(User $user)
    {
        $this->model = $user;
    }

    /**
     * Trouver un utilisateur par téléphone
     *
     * @param string $telephone
     * @return User|null
     */
    public function findByTelephone(string $telephone): ?User
    {
        return $this->model->where('telephone', $telephone)->first();
    }

    /**
     * Trouver un utilisateur par NCI
     *
     * @param string $nci
     * @return User|null
     */
    public function findByNci(string $nci): ?User
    {
        return $this->model->where('nci', $nci)->first();
    }
}