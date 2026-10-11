<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentReceiptStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:approved,rejected,pending',
            'notes'  => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'El estatus es obligatorio.',
            'status.in'       => 'El estatus seleccionado no es válido.',
            'notes.max'       => 'Las notas no pueden exceder los 500 caracteres.',
        ];
    }
}
