<?php

namespace App\Http\Requests\Admin;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

final class StaffRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $staff = $this->route('staff');
        $id = $staff instanceof User ? $staff->id : null;

        return [
            'name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:120', Rule::unique('users')->ignore($id)],
            'role' => ['required', Rule::enum(Role::class)],
            'document' => ['required', 'string', 'regex:/^[0-9]{5,15}$/', Rule::unique('users')->ignore($id)],
            'professional_registry' => ['nullable', 'required_if:role,'.Role::Therapist->value, 'string', 'max:30'],
            'phone' => ['nullable', 'string', 'regex:/^\+?[0-9 ]{7,15}$/'],
            'active' => ['boolean'],
            'password' => [$id === null ? 'required' : 'nullable', 'confirmed', Password::defaults()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'professional_registry.required_if' => 'El registro profesional (RETHUS) es obligatorio para fisioterapeutas.',
            'document.regex' => 'Escribe el documento solo con números.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function staff(): array
    {
        $data = [...$this->safe()->except(['password', 'password_confirmation']), 'active' => $this->boolean('active')];
        if ($this->filled('password')) {
            $data['password'] = $this->string('password')->toString();
        }

        return $data;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['email' => mb_strtolower(trim((string) $this->input('email')))]);
    }
}
