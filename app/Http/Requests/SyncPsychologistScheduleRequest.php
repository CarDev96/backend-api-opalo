<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncPsychologistScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'schedules'                         => 'required|array|min:1',
            'schedules.*.day_of_week'           => 'required|integer|between:1,6',
            'schedules.*.start_time'           => 'required|date_format:H:i',
            'schedules.*.end_time'             => 'required|date_format:H:i|after:schedules.*.start_time',
            'schedules.*.slot_duration_minutes' => 'nullable|integer|min:30|max:120',
            'schedules.*.is_active'             => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'schedules.required'              => 'La matriz de horarios es obligatoria.',
            'schedules.*.end_time.after'      => 'La hora de fin debe ser posterior a la hora de inicio.',
            'schedules.*.day_of_week.between' => 'El día de la semana debe ser de lunes (1) a sábado (6).',
        ];
    }
}
