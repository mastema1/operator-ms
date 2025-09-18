<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOperatorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'matricule' => ['nullable','string','max:255','unique:operators,matricule'],
            'first_name' => ['required','string','max:255'],
            'last_name' => ['required','string','max:255'],
            'poste_id' => ['required','integer','exists:postes,id'],
            'is_capable' => ['boolean'],
            'anciente' => ['nullable','string','max:255'],
            'type_de_contrat' => ['nullable','string','in:ANAPEC,AWRACH,TES,CDI,CDD 6 mois,CDD 1 ans,CDD 2 ans,CDD 3 ans'],
            'ligne' => ['nullable','string','in:Ligne 1,Ligne 2,Ligne 3,Ligne 4,Ligne 5,Ligne 6,Ligne 7,Ligne 8,Ligne 9,Ligne 10'],
            'is_critical' => ['boolean'],
        ];
    }

    public function prepareForValidation()
    {
        // Ensure poste_id is properly cast to integer
        if ($this->has('poste_id') && $this->poste_id !== '') {
            $this->merge([
                'poste_id' => (int) $this->poste_id
            ]);
        }
        
        // Handle checkbox values properly
        $this->merge([
            'is_capable' => $this->has('is_capable') ? true : false,
            'is_critical' => $this->has('is_critical') ? (bool) $this->is_critical : false,
        ]);
    }
} 