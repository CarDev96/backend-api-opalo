<?php

namespace App\Http\Requests;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Obtenemos el ID del rol de psicología para la regla condicional
        $psychologyRoleId = Role::where('name', 'psychology')->value('id');

        return [
            'name'     => ['required', 'string', 'max:150'],
            'email'    => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'role_id'  => ['required', 'uuid', 'exists:roles,id'],
            'password' => ['nullable', 'string', 'min:8'],
            'phone'    => [
                'nullable',
                'string',
                'regex:/^[0-9]{10}$/',
                'required_if:role_id,' . $psychologyRoleId,
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'             => 'El nombre completo es obligatorio.',
            'email.required'            => 'El correo electrónico es obligatorio.',
            'email.email'               => 'Ingresa un formato de correo electrónico válido.',
            'email.unique'              => 'El correo electrónico ya se encuentra registrado.',
            'role_id.required'          => 'Debes seleccionar un rol para el colaborador.',
            'role_id.exists'            => 'El rol seleccionado no es válido.',
            'password.min'              => 'La contraseña debe tener al menos 8 caracteres.',
            'phone.required_if'         => 'El número de teléfono es obligatorio para los psicólogos.',
            'phone.regex'               => 'El número de teléfono debe contener exactamente 10 dígitos numéricos.',
        ];
    }
}
