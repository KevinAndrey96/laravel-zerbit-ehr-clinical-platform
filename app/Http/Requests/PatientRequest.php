<?php

namespace App\Http\Requests;

use App\Enums\CivilStatus;
use App\Enums\DocumentType;
use App\Enums\Sex;
use App\Models\Patient;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class PatientRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $patient = $this->route('patient');

        return [
            'document_type' => ['required', Rule::enum(DocumentType::class)],
            'document_number' => [
                'required', 'string', 'regex:/^[0-9A-Z]{4,20}$/',
                Rule::unique('patients')
                    ->where('document_type', $this->string('document_type')->toString())
                    ->ignore($patient instanceof Patient ? $patient->id : null),
            ],
            'first_name' => ['required', 'string', 'max:60'],
            'second_name' => ['nullable', 'string', 'max:60'],
            'first_surname' => ['required', 'string', 'max:60'],
            'second_surname' => ['nullable', 'string', 'max:60'],
            'birthdate' => ['required', 'date', 'after:1900-01-01', 'before_or_equal:today'],
            'sex' => ['required', Rule::enum(Sex::class)],
            'civil_status' => ['nullable', Rule::enum(CivilStatus::class)],
            'insurer_id' => ['nullable', Rule::exists('insurers', 'id')->where('active', true)],
            'occupation' => ['nullable', 'string', 'max:80'],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9 ]{7,15}$/'],
            'email' => ['nullable', 'email', 'max:120'],
            'address' => ['nullable', 'string', 'max:150'],
            'city' => ['nullable', 'string', 'max:80'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'document_number.regex' => 'El número de documento solo admite letras mayúsculas y números, sin puntos.',
            'document_number.unique' => 'Ya existe un paciente con ese documento.',
            'phone.regex' => 'Escribe un teléfono de 7 a 15 dígitos.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'document_number' => strtoupper(preg_replace('/[\s.\-]/', '', (string) $this->input('document_number')) ?? ''),
            'email' => $this->filled('email') ? mb_strtolower(trim((string) $this->input('email'))) : null,
        ]);
    }
}
