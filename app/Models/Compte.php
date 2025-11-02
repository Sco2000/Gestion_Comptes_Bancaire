<?php

namespace App\Models;

use App\Models\Client;
use Illuminate\Support\Str;
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
                $model->id = (string) Str::uuid();
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
        // Seuls les comptes actifs peuvent être supprimés
        if ($newStatus === 'supprimé') {
            return $this->statut === 'actif';
        }

        // Seuls les comptes épargne peuvent être bloqués
        if ($newStatus === 'bloque') {
            return $this->type === 'epargne';
        }

        // Tous les comptes peuvent revenir à actif, sauf les comptes bloqués qui doivent d'abord être débloqués
        if ($newStatus === 'actif') {
            return true;
        }

        return false;
    }
}
