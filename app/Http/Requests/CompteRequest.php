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
<<<<<<< HEAD
            'client.prenom' => 'required|string|max:100',
            'client.nom' => 'required|string|max:100',
            'client.email' => 'required|email|unique:clients,email',
            'client.telephone' => ['required', new ValidTelephone(), 'unique:clients,telephone'],
            // 'client.adresse' => 'required|string|max:255',
            // 'client.nci' => ['nullable', new ValidNci()],
=======
            'client.nom' => 'required|string|max:100',
            'client.email' => 'required|email|unique:users,email',
            'client.telephone' => ['required', new ValidTelephone(), 'unique:users,telephone'],
            'client.adresse' => 'nullable|string|max:255',
            'client.date_naissance' => 'nullable|date',
>>>>>>> dev
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Le type de compte est obligatoire.',
            'type.in' => 'Le type de compte doit être cheque ou epargne.',
            'solde.required' => 'Le solde initial est obligatoire.',
            'solde.min' => 'Le solde initial doit être d\'au moins 10 000 FCFA.',
<<<<<<< HEAD
            // 'devise.in' => 'La devise doit être FCFA.',
            'client.prenom.required' => 'Le prénom du client est obligatoire.',
=======
>>>>>>> dev
            'client.nom.required' => 'Le nom du client est obligatoire.',
            'client.email.required' => 'L\'email du client est obligatoire.',
            'client.email.email' => 'L\'email du client n\'est pas valide.',
            'client.email.unique' => 'Cet email est déjà utilisé.',
            'client.telephone.required' => 'Le téléphone du client est obligatoire.',
            'client.telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
<<<<<<< HEAD
            // 'client.adresse.required' => 'L\'adresse du client est obligatoire.',
=======
>>>>>>> dev
        ];
    }
}

