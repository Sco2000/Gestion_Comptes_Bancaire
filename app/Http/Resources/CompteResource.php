<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'numeroCompte' => $this->numero_compte,
            'titulaire' => $this->client->user->nom,
            'type' => $this->type,
            'solde' => $this->solde,
            'devise' => 'FCFA',
            'dateCreation' => $this->created_at->toIso8601String(),
            'statut' => $this->statut,
            'metadata' => [
                'derniereModification' => $this->updated_at->toIso8601String(),
                'version' => 1,
            ]
        ];

        if ($this->statut === 'bloque') {
            $data['motifBlocage'] = $this->motif_blocage;
            $data['dateDebutBlocage'] = $this->date_debut_blocage;
            $data['dateFinBlocage'] = $this->date_fin_blocage;
        }

        return $data;
    }
}
