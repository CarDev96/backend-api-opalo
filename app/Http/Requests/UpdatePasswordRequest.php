<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdatePasswordRequest extends FormRequest
{
    /**
     * Determina si el usuario autenticado está autorizado.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Reglas de validación.
     */
    public function rules(): array
    {
        return [
            'current_password' => [
                'required',
                'string',
                'current_password', // Valida automáticamente contra el hash del usuario autenticado
            ],
            'password' => [
                'required',
                'string',
                'confirmed',
                'different:current_password',
                Password::min(8),
            ],
            'password_confirmation' => [
                'required',
                'string',
            ],
        ];
    }

    /**
     * Mensajes descriptivos en español.
     */
    public function messages(): array
    {
        return [
            'current_password.required'         => 'Debes ingresar tu contraseña actual.',
            'current_password.current_password' => 'La contraseña actual no coincide con nuestros registros.',
            'password.required'                 => 'Ingresa la nueva contraseña.',
            'password.min'                      => 'La nueva contraseña debe contener al menos 8 caracteres.',
            'password.confirmed'                => 'La confirmación de la contraseña no coincide.',
            'password.different'                => 'La nueva contraseña debe ser distinta a la actual.',
            'password_confirmation.required'    => 'Confirma tu nueva contraseña.',
        ];
    }
}
