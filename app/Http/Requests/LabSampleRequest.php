<?php

namespace App\Http\Requests;

use App\Enums\SampleType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class LabSampleRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'integer', Rule::exists('patients', 'id')],
            'sample_type' => ['required', Rule::enum(SampleType::class)],
            'exam' => ['required', 'string', 'max:120'],
            'authorization_number' => ['nullable', 'string', 'max:30'],
            'taken_on' => ['required', 'date', 'before_or_equal:today'],
            'notify_by_email' => ['boolean'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function sample(): array
    {
        return [...$this->safe()->except('patient_id'), 'notify_by_email' => $this->boolean('notify_by_email')];
    }
}
