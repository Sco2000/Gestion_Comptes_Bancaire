<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class NonArchiveScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        // On exclut les comptes supprimés
        $builder->whereNotIn('statut', ['supprimé', 'bloque']);
    }
}
