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
        try {
            \Log::info("Starting restoration of archived compte: {$this->compteId}");

            // Check if compte already exists in main database
            $existingCompte = DB::connection('pgsql')->table('comptes')->where('id', $this->compteId)->first();
            if ($existingCompte) {
                \Log::warning("Compte {$this->compteId} already exists in main database, skipping restoration");
                return;
            }

            $neonCompte = DB::connection('neon')->table('comptes')->where('id', $this->compteId)->first();

            if (!$neonCompte) {
                \Log::warning("Compte {$this->compteId} not found in Neon database");
                return;
            }

            if ($neonCompte->statut !== 'supprimé') {
                \Log::warning("Compte {$this->compteId} is not supprimé (status: {$neonCompte->statut})");
                return;
            }

            DB::transaction(function () use ($neonCompte) {
                try {
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

                    \Log::info("Successfully restored compte {$neonCompte->id} from Neon to main database");
                } catch (\Exception $e) {
                    \Log::error("Transaction failed for compte {$neonCompte->id}: " . $e->getMessage());
                    throw $e; // Re-throw to rollback transaction
                }
            });
        } catch (\Exception $e) {
            \Log::error("Failed to restore archived compte {$this->compteId}: " . $e->getMessage());
            throw $e; // Re-throw to mark job as failed
        }
    }
}
