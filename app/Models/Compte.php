<?php

namespace App\Models;

use App\Models\Client;
use App\Models\Scopes\NonArchiveScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Compte extends Model
{
    use HasFactory;
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'client_id',
        'numero_compte',
        'type',
        'solde',
        'date_creation',
        'statut',
        'date_debut_blocage',
        'date_fin_blocage',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new NonArchiveScope);
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) \Illuminate\Support\Str::uuid();
            }
        });

        // Handle status changes
        static::updating(function ($model) {
            if ($model->isDirty('statut')) {
                $originalStatus = $model->getOriginal('statut');
                $newStatus = $model->statut;

                if ($newStatus === 'bloque' && $originalStatus !== 'bloque') {
                    // Dispatch job to move blocked compte to Neon
                    \App\Jobs\MoveBlockedCompteToNeon::dispatch($model->id);
                } elseif ($newStatus === 'actif' && $originalStatus === 'bloque') {
                    // Dispatch job to restore unblocked compte from Neon
                    \App\Jobs\RestoreUnblockedCompteFromNeon::dispatch($model->id);
                }
            }
        });
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function canChangeStatus($newStatus)
    {
        if ($this->type === 'cheque') {
            return in_array($newStatus, ['actif', 'archive']);
        }

        // Pour les comptes épargne
        if ($this->type === 'epargne') {
            // Un compte épargne bloqué ne peut pas être archivé
            if ($this->statut === 'bloque' && $newStatus === 'archive') {
                return false;
            }

            return in_array($newStatus, ['actif', 'bloque', 'archive']);
        }

        return false;
    }
}
