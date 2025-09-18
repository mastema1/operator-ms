<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePosteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required','string','max:255'],
            'ligne' => ['nullable','string','in:Ligne 1,Ligne 2,Ligne 3,Ligne 4,Ligne 5,Ligne 6,Ligne 7,Ligne 8,Ligne 9,Ligne 10'],
        ];
    }
}
