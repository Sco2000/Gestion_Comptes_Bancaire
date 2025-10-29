<?php

namespace App\Jobs;

use App\Models\Compte;
use App\Models\NeonCompte;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class RestoreArchivedCompteFromNeon implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $compteId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $compteId)
    {
        $this->compteId = $compteId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $neonCompte = DB::connection('neon')->table('comptes')->where('id', $this->compteId)->first();

        if (!$neonCompte || $neonCompte->statut !== 'archive') {
            return;
        }

        DB::transaction(function () use ($neonCompte) {
            // Copy compte back to Render
            DB::connection('pgsql')->table('comptes')->insert([
                'id' => $neonCompte->id,
                'client_id' => $neonCompte->client_id,
                'numero_compte' => $neonCompte->numero_compte,
                'type' => $neonCompte->type,
                'solde' => $neonCompte->solde,
                'date_creation' => $neonCompte->date_creation,
                'statut' => 'actif',
                'date_debut_blocage' => null,
                'date_fin_blocage' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Delete from Neon
            DB::connection('neon')->table('comptes')->where('id', $neonCompte->id)->delete();
        });
    }
}
