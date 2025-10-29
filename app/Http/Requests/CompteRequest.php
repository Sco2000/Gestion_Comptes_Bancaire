<?php

namespace App\Http\Requests;

use App\Rules\ValidTelephone;
use Illuminate\Foundation\Http\FormRequest;

class CompteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
        public function rules(): array
    {
        return [
            'type' => 'required|in:cheque,epargne',
            'solde' => 'required|numeric|min:10000',
            'client.prenom' => 'nullable|string|max:100',
            'client.nom' => 'required|string|max:100',
            'client.email' => 'required|email',
            'client.telephone' => ['required', new ValidTelephone()],
            'client.adresse' => 'nullable|string|max:255',
            'client.nci' => 'nullable|string|max:50|unique:clients,nci',
            'client.date_naissance' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Le type de compte est obligatoire.',
            'type.in' => 'Le type de compte doit être cheque ou epargne.',
            'solde.required' => 'Le solde initial est obligatoire.',
            'solde.min' => 'Le solde initial doit être d\'au moins 10 000 FCFA.',
            'client.prenom.required' => 'Le prénom du client est obligatoire.',
            'client.nom.required' => 'Le nom du client est obligatoire.',
            'client.email.required' => 'L\'email du client est obligatoire.',
            'client.email.email' => 'L\'email du client n\'est pas valide.',
            'client.telephone.required' => 'Le téléphone du client est obligatoire.',
            'client.nci.unique' => 'Ce numéro CNI est déjà utilisé.',
        ];
    }
}
