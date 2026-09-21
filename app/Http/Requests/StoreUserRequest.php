<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    /**
     * Determina si el usuario autenticado tiene permisos para realizar esta acción.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación para el alta de colaboradores internos.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
            ],
            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                'max:255',
                // Evita duplicados ignorando los eliminados lógicamente si fuera necesario,
                // o estricto sobre toda la tabla:
                'unique:users,email',
            ],
            'role_id' => [
                'required',
                'string',
                // Valida que el rol exista en la tabla roles y NO sea el de cliente
                Rule::exists('roles', 'id')->where(function ($query) {
                    $query->where('name', '!=', 'client');
                }),
            ],
            'password' => [
                'nullable',
                'string',
                'min:8',
                'max:100',
            ],
        ];
    }

    /**
     * Limpieza y normalización de datos antes de validar.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name'  => $this->name ? trim(preg_replace('/\s+/', ' ', $this->name)) : null,
            'email' => $this->email ? strtolower(trim($this->email)) : null,
        ]);
    }

    /**
     * Mensajes descriptivos de validación en español.
     */
    public function messages(): array
    {
        return [
            'name.required'    => 'El nombre completo del colaborador es obligatorio.',
            'name.min'         => 'El nombre debe tener al menos 3 caracteres.',
            'email.required'   => 'El correo electrónico institucional es obligatorio.',
            'email.email'      => 'Ingresa un formato de correo válido (ej. usuario@opalo.com).',
            'email.unique'     => 'Este correo electrónico ya está registrado en el sistema.',
            'role_id.required' => 'Debes asignar un perfil o especialidad (Psicología, T. Social, Ventas, etc.).',
            'role_id.exists'   => 'El perfil seleccionado no es válido para colaboradores internos.',
            'password.min'     => 'Si asignas una contraseña manual, debe tener al menos 8 caracteres.',
        ];
    }
}
