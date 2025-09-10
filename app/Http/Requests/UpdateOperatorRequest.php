<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOperatorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $operatorId = $this->route('operator')?->id ?? $this->route('id');

        return [
            'matricule' => ['nullable','string','max:255', Rule::unique('operators','matricule')->ignore($operatorId)],
            'first_name' => ['required','string','max:255'],
            'last_name' => ['required','string','max:255'],
            'poste_id' => ['required','exists:postes,id'],
            'is_capable' => ['boolean'],
            'anciente' => ['nullable','string','max:255'],
            'type_de_contrat' => ['nullable','string','in:ANAPEC,AWRACH,TES,CDI,CDD 6 mois,CDD 1 ans,CDD 2 ans,CDD 3 ans'],
            'ligne' => ['nullable','string','in:Ligne 1,Ligne 2,Ligne 3,Ligne 4,Ligne 5,Ligne 6,Ligne 7,Ligne 8,Ligne 9,Ligne 10'],
            'is_critical' => ['boolean'],
        ];
    }
} 