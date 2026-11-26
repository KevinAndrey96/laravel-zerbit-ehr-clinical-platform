<?php

namespace App\Http\Requests\Clinical;

use App\Domain\Clinical\Cie10Code;
use App\Enums\FallRisk;
use App\Models\ClinicalHistory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

final class ClinicalHistoryRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $optional = ['nullable', 'string', 'max:2000'];
        $rules = [
            'opened_on' => ['required', 'date', 'before_or_equal:today'],
            'reason' => ['required', 'string', 'max:2000'],
            'diagnosis_code' => ['required', 'string', 'max:8', function (string $attribute, mixed $value, \Closure $fail): void {
                try {
                    Cie10Code::parse((string) $value);
                } catch (InvalidArgumentException) {
                    $fail('Escribe un código CIE-10 válido, por ejemplo M54.5.');
                }
            }],
            'diagnosis' => ['required', 'string', 'max:500'],
            'physio_diagnosis' => ['required', 'string', 'max:2000'],
            'objective' => ['required', 'string', 'max:2000'],
            'authorized_sessions' => ['required', 'integer', 'between:1,60'],
            'authorization_number' => ['nullable', 'string', 'max:30'],
            'copayment_cop' => ['nullable', 'integer', 'between:0,10000000'],
            'pain_scale' => ['nullable', 'integer', 'between:0,10'],
            'pain_notes' => $optional,
            'fall_risk' => ['nullable', Rule::enum(FallRisk::class)],
        ];
        foreach ([...array_keys(ClinicalHistory::BACKGROUND), ...array_keys(ClinicalHistory::ASSESSMENT)] as $field) {
            $rules[$field] = $optional;
        }

        return $rules;
    }

    /**
     * @return array<string, mixed>
     */
    public function clinicalData(): array
    {
        $data = $this->validated();
        $data['diagnosis_code'] = Cie10Code::parse($this->string('diagnosis_code')->toString())->value;

        return $data;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['copayment_cop' => $this->filled('copayment_cop')
            ? preg_replace('/[^0-9]/', '', (string) $this->input('copayment_cop'))
            : null]);
    }
}
