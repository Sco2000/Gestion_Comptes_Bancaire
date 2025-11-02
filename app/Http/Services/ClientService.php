<?php

namespace App\Http\Services;

use App\Enums\ErrorCode;
use App\Enums\HttpStatusCode;
use App\Exceptions\CustomApiException;
use App\Http\Repositories\UserRepository;
use App\Models\User;

class ClientService
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Récupérer un client par téléphone
     *
     * @param string $telephone
     * @return User|null
     */
    public function getClientByTelephone(string $telephone): ?User
    {
        $user = $this->userRepository->findByTelephone($telephone);

        // Vérifier que l'utilisateur est bien un client
        if ($user && !$user->isClient()) {
            return null;
        }

        return $user;
    }

    /**
     * Récupérer un client par NCI
     *
     * @param string $nci
     * @return User|null
     */
    public function getClientByNci(string $nci): ?User
    {
        $user = $this->userRepository->findByNci($nci);

        // Vérifier que l'utilisateur est bien un client
        if ($user && !$user->isClient()) {
            return null;
        }

        return $user;
    }
}