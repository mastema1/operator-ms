<?php

namespace App\Http\Requests;

use App\Services\SecurityService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

abstract class SecureBaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->tenant_id;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    abstract public function rules(): array;

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize search inputs
        if ($this->has('search')) {
            $this->merge([
                'search' => SecurityService::sanitizeSearchInput($this->input('search'))
            ]);
        }

        // Sanitize name fields
        $nameFields = ['name', 'first_name', 'last_name', 'matricule'];
        foreach ($nameFields as $field) {
            if ($this->has($field)) {
                $value = $this->input($field);
                if (is_string($value)) {
                    $this->merge([
                        $field => trim(strip_tags($value))
                    ]);
                }
            }
        }

        // Validate tenant ownership for resource updates
        if ($this->route() && in_array($this->method(), ['PUT', 'PATCH', 'DELETE'])) {
            $this->validateTenantOwnership();
        }
    }

    /**
     * Validate that the resource being modified belongs to the current tenant.
     */
    protected function validateTenantOwnership(): void
    {
        $routeName = $this->route()->getName();
        $resourceId = null;
        $modelName = null;

        // Extract model and ID from route
        if (str_contains($routeName, 'operators.')) {
            $modelName = 'Operator';
            $resourceId = $this->route('operator')?->id;
        } elseif (str_contains($routeName, 'postes.')) {
            $modelName = 'Poste';
            $resourceId = $this->route('poste')?->id;
        } elseif (str_contains($routeName, 'backup.')) {
            $modelName = 'BackupAssignment';
            $resourceId = $this->route('assignment')?->id;
        }

        if ($modelName && $resourceId) {
            if (!SecurityService::validateTenantOwnership($modelName, $resourceId)) {
                SecurityService::logSecurityEvent('Unauthorized resource access attempt', [
                    'model' => $modelName,
                    'resource_id' => $resourceId,
                    'route' => $routeName
                ]);

                throw ValidationException::withMessages([
                    'authorization' => ['You are not authorized to access this resource.']
                ]);
            }
        }
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'required' => 'The :attribute field is required.',
            'string' => 'The :attribute must be a valid text string.',
            'max' => 'The :attribute may not be greater than :max characters.',
            'min' => 'The :attribute must be at least :min characters.',
            'email' => 'The :attribute must be a valid email address.',
            'unique' => 'The :attribute has already been taken.',
            'exists' => 'The selected :attribute is invalid.',
            'in' => 'The selected :attribute is invalid.',
            'numeric' => 'The :attribute must be a number.',
            'integer' => 'The :attribute must be an integer.',
            'boolean' => 'The :attribute must be true or false.',
            'date' => 'The :attribute must be a valid date.',
            'regex' => 'The :attribute format is invalid.',
        ];
    }

    /**
     * Get custom attribute names for validation errors.
     */
    public function attributes(): array
    {
        return [
            'first_name' => 'first name',
            'last_name' => 'last name',
            'poste_id' => 'position',
            'backup_operator_id' => 'backup operator',
            'operator_id' => 'operator',
            'tenant_id' => 'organization',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        SecurityService::logSecurityEvent('Validation failed', [
            'route' => $this->route()?->getName(),
            'errors' => $validator->errors()->toArray(),
            'input' => $this->except(['password', 'password_confirmation', '_token'])
        ]);

        parent::failedValidation($validator);
    }
}
