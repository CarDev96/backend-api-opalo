<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('id') ?? $this->route('user');

        return [
            'name'    => ['required', 'string', 'max:255'],
            'email'   => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'role_id' => ['required', 'exists:roles,id'],
            'phone'   => ['nullable', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'    => 'El nombre del colaborador es obligatorio.',
            'email.required'   => 'El correo institucional es obligatorio.',
            'email.unique'     => 'Este correo electrónico ya está registrado por otro colaborador.',
            'role_id.required' => 'Debes seleccionar un rol para el colaborador.',
            'role_id.exists'   => 'El rol seleccionado no es válido.',
        ];
    }
}
