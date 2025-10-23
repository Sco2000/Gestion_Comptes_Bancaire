<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compte extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'numero_compte',
        'titulaire',
        'type',
        'solde',
        'devise',
        'date_creation',
        'statut',
        'motif_blocage',
        'metadata',
    ];

    protected $casts = [
        'solde' => 'decimal:2',
        'date_creation' => 'datetime',
        'metadata' => 'array',
    ];
}
