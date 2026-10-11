<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && (bool) auth()->user()->client_id;
    }

    public function rules(): array
    {
        return [
            'examinee_id'        => 'required|uuid|exists:examinees,id',
            'psychologist_id'    => 'required|uuid|exists:users,id',
            'evaluation_type_id' => 'required|uuid|exists:evaluation_types,id',
            'date'               => 'required|date|after_or_equal:today',
            'start_time'         => 'required|date_format:H:i',
            'notes'              => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'examinee_id.required'        => 'Debes seleccionar un evaluado.',
            'psychologist_id.required'    => 'Debes seleccionar un psicólogo.',
            'evaluation_type_id.required' => 'Debes seleccionar el tipo de evaluación.',
            'date.required'               => 'La fecha es obligatoria.',
            'date.after_or_equal'         => 'La fecha de la cita no puede ser en el pasado.',
            'start_time.required'         => 'Debes seleccionar un horario disponible.',
        ];
    }
}
