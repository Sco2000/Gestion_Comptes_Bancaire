<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;


class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Passport::routes();

        // Définir les scopes pour les permissions
        Passport::tokensCan([
            'comptes.read' => 'Lire les comptes',
            'comptes.write' => 'Écrire les comptes',
            'comptes.delete' => 'Supprimer les comptes',
            'clients.read' => 'Lire les clients',
            'clients.write' => 'Écrire les clients',
            'users.read' => 'Lire les utilisateurs',
            'users.write' => 'Écrire les utilisateurs',
        ]);

        // Personnaliser les claims du token
        Passport::setDefaultScope([
            'comptes.read',
        ]);
    }
}
