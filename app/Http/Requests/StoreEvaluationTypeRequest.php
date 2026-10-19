<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEvaluationTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:150', 'unique:evaluation_types,name'],
            'modality'     => ['required', 'string', 'in:Presencial,Remota'],
            'cost'         => ['required', 'numeric', 'min:0'],
            'description'  => ['nullable', 'string', 'max:500'],
            'includes_vat' => ['boolean'],
            'is_active'    => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'El nombre del tipo de evaluación es obligatorio.',
            'name.unique'       => 'Ya existe un tipo de evaluación con este nombre.',
            'modality.required' => 'La modalidad es requerida (Presencial o Remota).',
            'cost.required'     => 'El costo base es obligatorio.',
            'cost.numeric'      => 'El costo debe ser un valor numérico válido.',
        ];
    }
}
