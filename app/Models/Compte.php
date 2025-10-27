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
    ];

    protected static function booted()
    {
        static::addGlobalScope(new NonArchiveScope);
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) \Illuminate\Support\Str::uuid();
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

        // Pour les comptes épargne, tous les statuts sont autorisés
        return in_array($newStatus, ['actif', 'bloque', 'archive']);
    }
}
