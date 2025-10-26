<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    public $incrementing = false; // UUID
    protected $keyType = 'string';
    protected $fillable = ['id','prenom','nom','email','telephone','adresse','nci'];
    
    public function comptes()
    {
        return $this->hasMany(Compte::class);
    }
}
