<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NeonCompte extends Model
{
    use HasFactory;

    protected $connection = 'neon';

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
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    // No relationships in Neon models - data is stored independently
}