<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NeonClient extends Model
{
    use HasFactory;

    protected $connection = 'neon';

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id', 'user_id', 'prenom', 'nom', 'email', 'telephone', 'adresse', 'nci', 'date_naissance'];

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