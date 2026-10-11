<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppointmentSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:scheduled,attended,cancelled,no_show',
            'notes'  => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'El estatus de la cita es obligatorio.',
            'status.in'       => 'El estatus seleccionado no es válido.',
            'notes.max'       => 'Las notas no pueden exceder los 1000 caracteres.',
        ];
    }
}
