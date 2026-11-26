<?php

namespace App\Http\Requests\Clinical;

use App\Models\ClinicalHistory;
use Illuminate\Foundation\Http\FormRequest;

final class EvolutionRequest extends FormRequest
{
    protected $errorBag = 'evolution';

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $history = $this->route('history');
        $opened = $history instanceof ClinicalHistory ? $history->opened_on->toDateString() : 'today';

        return [
            'attended_on' => ['required', 'date', 'after_or_equal:'.$opened, 'before_or_equal:today'],
            'starts_at' => ['required', 'date_format:H:i'],
            'ends_at' => ['required', 'date_format:H:i', 'after:starts_at'],
            'pain_scale' => ['nullable', 'integer', 'between:0,10'],
            'note' => ['required', 'string', 'min:20', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'attended_on.after_or_equal' => 'La sesión no puede ser anterior a la apertura de la historia.',
            'ends_at.after' => 'La hora de fin debe ser posterior a la de inicio.',
            'note.min' => 'Describe la sesión con al menos 20 caracteres: intervención, respuesta del paciente y plan.',
        ];
    }
}
