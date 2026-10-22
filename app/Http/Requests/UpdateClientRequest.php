<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $clientId = $this->route('id') ?? $this->route('client');

        return [
            'company_name'            => ['required', 'string', 'max:255'],
            'trade_name'              => ['nullable', 'string', 'max:255'],
            'rfc'                     => [
                'required',
                'string',
                'max:13',
                Rule::unique('clients', 'rfc')->ignore($clientId),
            ],
            'fiscal_regime'           => ['nullable', 'string', 'max:255'],
            'contact_email'           => [
                'required',
                'email',
                'max:255',
                Rule::unique('clients', 'contact_email')->ignore($clientId),
            ],
            'contact_phone'           => ['nullable', 'string', 'max:20'],
            'requires_purchase_order' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'company_name.required'  => 'La razón social es obligatoria.',
            'rfc.required'           => 'El RFC es obligatorio.',
            'rfc.unique'             => 'El RFC ya pertenece a otra empresa.',
            'contact_email.required' => 'El correo de contacto es obligatorio.',
            'contact_email.unique'   => 'Este correo electrónico ya está registrado por otra empresa.',
        ];
    }
}
