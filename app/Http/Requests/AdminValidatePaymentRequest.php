<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminValidatePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Solo usuarios con rol admin o sales pueden validar
        return in_array($this->user()->role?->name, ['admin', 'sales']);
    }

    public function rules(): array
    {
        return [
            'status'           => ['required', 'string', 'in:approved,rejected'],
            'rejection_reason' => ['required_if:status,rejected', 'nullable', 'string', 'max:500'],
            'notes'            => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required'                 => 'Debes indicar si el pago es aprobado o rechazado.',
            'status.in'                       => 'El estatus debe ser approved o rejected.',
            'rejection_reason.required_if'    => 'Debes especificar el motivo si rechazas el comprobante.',
        ];
    }
}
