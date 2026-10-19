<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEvaluationTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('evaluation_type') ?? $this->route('id');

        return [
            'name'         => ['required', 'string', 'max:150', Rule::unique('evaluation_types', 'name')->ignore($id)],
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
            'name.required' => 'El nombre del tipo de evaluación es obligatorio.',
            'name.unique'   => 'Ya existe un tipo de evaluación con este nombre.',
            'cost.required' => 'El costo base es obligatorio.',
        ];
    }
}
