<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Passport\HasApiTokens;
use Laravel\Passport\Passport;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Désactiver l'auto-incrémentation de l'ID.
     */
    public $incrementing = false;

    /**
     * Spécifier le type de la clé primaire (UUID = string).
     */
    protected $keyType = 'string';

    /**

     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'password',
        'telephone',
        'actif',
        'login',
        'plain_password',
        'nci',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'plain_password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'actif' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function client()
    {
        return $this->hasOne(Client::class);
    }

    public function admin()
    {
        return $this->hasOne(Admin::class);
    }

    public function isAdmin()
    {
        return $this->admin()->exists();
    }

    public function isClient()
    {
        return $this->client()->exists();
    }

    /**
     * Définir les scopes pour les permissions
     */
    public function scopeWithScopes($query, array $scopes)
    {
        return $query->whereHas('tokens', function ($tokenQuery) use ($scopes) {
            $tokenQuery->whereJsonContains('scopes', $scopes);
        });
    }

    /**
     * Obtenir le rôle de l'utilisateur pour les claims personnalisés
     */
    public function getRoleAttribute()
    {
        if ($this->isAdmin()) {
            return 'admin';
        } elseif ($this->isClient()) {
            return 'client';
        }
        return 'user';
    }

    /**
     * Obtenir les permissions de l'utilisateur
     */
    public function getPermissionsAttribute()
    {
        $permissions = [];

        if ($this->isAdmin()) {
            $permissions = [
                'comptes.read',
                'comptes.write',
                'comptes.delete',
                'clients.read',
                'clients.write',
                'users.read',
                'users.write',
            ];
        } elseif ($this->isClient()) {
            $permissions = [
                'comptes.read',
                'comptes.write',
            ];
        }

        return $permissions;
    }
}
