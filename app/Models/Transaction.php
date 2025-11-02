<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero_transaction',
        'date',
        'type',
        'montant',
        'solde_apres',
        'description',
        'client_id',
    ];

    protected $casts = [
        'date' => 'datetime',
        'montant' => 'decimal:2',
        'solde_apres' => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
