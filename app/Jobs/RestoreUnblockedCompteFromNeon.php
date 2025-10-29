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

class RestoreUnblockedCompteFromNeon implements ShouldQueue
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

        if (!$neonCompte || $neonCompte->statut !== 'bloque') {
            return;
        }

        // Calculate delay based on date_fin_blocage
        $now = now();
        $delay = $neonCompte->date_fin_blocage ? $now->diffInSeconds($neonCompte->date_fin_blocage, false) : 0;

        if ($delay > 0) {
            // Schedule for later
            $this->release($delay);
            return;
        }

        // Immediate restoration
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
