<?php

namespace App\Http\Requests;

use App\Enums\Role;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class AppointmentRequest extends FormRequest
{
    public const array DURATIONS = [30, 45, 60];

    private const int OPENS_AT = 6;

    private const int CLOSES_AT = 20;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'integer', Rule::exists('patients', 'id')],
            'therapist_id' => ['required', 'integer', Rule::exists('users', 'id')->where('role', Role::Therapist->value)->where('active', true)],
            'starts_at' => ['required', 'date_format:Y-m-d\TH:i', 'after:now'],
            'duration' => ['required', 'integer', Rule::in(self::DURATIONS)],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['starts_at.after' => 'La cita debe quedar en el futuro.'];
    }

    /**
     * @return list<callable(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $start = CarbonImmutable::parse($this->string('starts_at')->toString());
            $end = $start->addMinutes($this->integer('duration'));
            if ($start->isSunday() || $start->hour < self::OPENS_AT || $end->greaterThan($start->setTime(self::CLOSES_AT, 0))) {
                $validator->errors()->add('starts_at', 'Atendemos de lunes a sábado entre las 6:00 a. m. y las 8:00 p. m.');
            }
        }];
    }

    /**
     * @return array{patient_id: int, therapist_id: int, starts_at: string, duration: int, notes: string|null}
     */
    public function appointment(): array
    {
        return [
            'patient_id' => $this->integer('patient_id'),
            'therapist_id' => $this->integer('therapist_id'),
            'starts_at' => $this->string('starts_at')->toString(),
            'duration' => $this->integer('duration'),
            'notes' => $this->filled('notes') ? $this->string('notes')->toString() : null,
        ];
    }
}
