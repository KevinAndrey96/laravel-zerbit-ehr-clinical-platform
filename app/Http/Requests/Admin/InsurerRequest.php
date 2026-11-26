<?php

namespace App\Http\Requests\Admin;

use App\Domain\Tax\Nit;
use App\Models\Insurer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use InvalidArgumentException;

final class InsurerRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $insurer = $this->route('insurer');

        return [
            'name' => ['required', 'string', 'max:120'],
            'nit' => ['required', 'string', 'max:20', Rule::unique('insurers')->ignore($insurer instanceof Insurer ? $insurer->id : null)],
            'active' => ['boolean'],
        ];
    }

    /**
     * @return list<callable(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->has('nit')) {
                return;
            }
            try {
                Nit::parse($this->string('nit')->toString());
            } catch (InvalidArgumentException) {
                $validator->errors()->add('nit', 'El NIT no es válido. Revisa el número y el dígito de verificación.');
            }
        }];
    }

    /**
     * @return array{name: string, nit: string, active: bool}
     */
    public function insurer(): array
    {
        return [
            'name' => $this->string('name')->toString(),
            'nit' => (string) Nit::parse($this->string('nit')->toString()),
            'active' => $this->boolean('active'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $nit = $this->input('nit');
        if (is_string($nit)) {
            try {
                $this->merge(['nit' => (string) Nit::parse($nit)]);
            } catch (InvalidArgumentException) {
                // The after hook reports it with a friendly message.
            }
        }
    }
}
