<?php

namespace App\Jobs;

use App\Models\Compte;
use App\Models\NeonClient;
use App\Models\NeonCompte;
use App\Models\NeonUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class MoveArchivedComptesToNeon implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $archivedComptes = Compte::where('statut', 'archive')->get();

        foreach ($archivedComptes as $compte) {
            DB::transaction(function () use ($compte) {
                // Copy client and user if not exists
                $this->copyClientToNeon($compte->client);

                // Copy compte to Neon
                NeonCompte::create([
                    'id' => $compte->id,
                    'client_id' => $compte->client_id,
                    'numero_compte' => $compte->numero_compte,
                    'type' => $compte->type,
                    'solde' => $compte->solde,
                    'date_creation' => $compte->date_creation,
                    'statut' => 'archive',
                    'date_debut_blocage' => $compte->date_debut_blocage,
                    'date_fin_blocage' => $compte->date_fin_blocage,
                ]);

                // Delete from Render
                $compte->delete();
            });
        }
    }

    private function copyClientToNeon($client)
    {
        // Copy user if not exists
        $neonUser = NeonUser::find($client->user_id);
        if (!$neonUser) {
            $user = $client->user;
            NeonUser::create([
                'id' => $user->id,
                'nom' => $user->nom,
                'email' => $user->email,
                'password' => $user->password,
                'telephone' => $user->telephone,
                'actif' => $user->actif,
                'email_verified_at' => $user->email_verified_at,
            ]);

            // Copy admin if exists
            if ($user->admin) {
                \App\Models\NeonAdmin::create([
                    'id' => $user->admin->id,
                    'user_id' => $user->admin->user_id,
                    'matricule' => $user->admin->matricule,
                ]);
            }
        }

        // Copy client if not exists
        $neonClient = NeonClient::find($client->id);
        if (!$neonClient) {
            NeonClient::create([
                'id' => $client->id,
                'user_id' => $client->user_id,
                'prenom' => $client->prenom,
                'nom' => $client->nom,
                'email' => $client->email,
                'telephone' => $client->telephone,
                'adresse' => $client->adresse,
                'nci' => $client->nci,
                'date_naissance' => $client->date_naissance,
            ]);
        }
    }
}
