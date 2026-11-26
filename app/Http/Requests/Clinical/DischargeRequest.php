<?php

namespace App\Http\Requests\Clinical;

use App\Models\ClinicalHistory;
use Illuminate\Foundation\Http\FormRequest;

final class DischargeRequest extends FormRequest
{
    protected $errorBag = 'discharge';

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $history = $this->route('history');
        $opened = $history instanceof ClinicalHistory ? $history->opened_on->toDateString() : 'today';

        return [
            'closed_on' => ['required', 'date', 'after_or_equal:'.$opened, 'before_or_equal:today'],
            'discharge_summary' => ['required', 'string', 'min:20', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['closed_on.after_or_equal' => 'El alta no puede ser anterior a la apertura de la historia.'];
    }
}
