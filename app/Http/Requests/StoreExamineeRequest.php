<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExamineeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->client_id !== null;
    }

    public function rules(): array
    {
        return [
            'full_name' => 'required|string|max:255',
            'position'  => 'required|string|max:150',
            'phone'     => 'required|string|max:20',
            'email'     => 'nullable|email|max:255',
            'curp'      => 'nullable|string|size:18',
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'El nombre completo del evaluado es obligatorio.',
            'position.required'  => 'El puesto o categoría a evaluar es obligatorio.',
            'phone.required'     => 'El número de teléfono es obligatorio para contactar al evaluado.',
            'email.email'        => 'El formato del correo electrónico no es válido.',
            'curp.size'          => 'La CURP debe tener exactamente 18 caracteres.',
        ];
    }
}
