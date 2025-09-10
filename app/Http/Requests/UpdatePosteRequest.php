<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePosteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $posteId = $this->route('poste')?->id ?? $this->route('id');

        return [
            'name' => ['required','string','max:255'],
            'is_critical' => ['boolean'],
            'ligne' => ['nullable','string','in:Ligne 1,Ligne 2,Ligne 3,Ligne 4,Ligne 5,Ligne 6,Ligne 7,Ligne 8,Ligne 9,Ligne 10'],
        ];
    }
}
