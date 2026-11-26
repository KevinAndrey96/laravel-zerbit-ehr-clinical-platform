<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Insurer;
use App\Models\User;
use Tests\TestCase;

final class AdminTest extends TestCase
{
    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function staff(array $overrides = []): array
    {
        return [
            'name' => 'Natalia',
            'last_name' => 'Ospina',
            'email' => 'Natalia.Ospina@zerbit.test',
            'document' => '1030111222',
            'role' => Role::Therapist->value,
            'professional_registry' => 'RETHUS-123456',
            'active' => '1',
            'password' => 'ClaveSegura2026',
            'password_confirmation' => 'ClaveSegura2026',
            ...$overrides,
        ];
    }

    public function test_front_desk_creates_a_therapist_account(): void
    {
        $this->actingAs($this->admin())->post('/admin/personal', $this->staff())->assertRedirect('/admin/personal');

        $user = User::query()->where('email', 'natalia.ospina@zerbit.test')->sole();
        $this->assertTrue($user->isTherapist());
        $this->post('/salir');
        $this->post('/ingresar', ['email' => 'natalia.ospina@zerbit.test', 'password' => 'ClaveSegura2026'])->assertRedirect('/panel');
    }

    public function test_therapists_need_a_professional_registry(): void
    {
        $this->actingAs($this->admin())->post('/admin/personal', $this->staff(['professional_registry' => '']))
            ->assertSessionHasErrors('professional_registry');
    }

    public function test_weak_passwords_are_rejected(): void
    {
        $this->actingAs($this->admin())->post('/admin/personal', $this->staff(['password' => 'corta', 'password_confirmation' => 'corta']))
            ->assertSessionHasErrors('password');
    }

    public function test_editing_keeps_the_password_when_left_blank(): void
    {
        $therapist = $this->therapist();
        $hash = $therapist->password;

        $this->actingAs($this->admin())->put("/admin/personal/{$therapist->id}", $this->staff([
            'email' => $therapist->email, 'document' => $therapist->document, 'password' => '', 'password_confirmation' => '', 'active' => '0',
        ]))->assertRedirect('/admin/personal');

        $therapist->refresh();
        $this->assertSame($hash, $therapist->password);
        $this->assertFalse($therapist->active);
    }

    public function test_an_administrator_cannot_lock_themselves_out(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put("/admin/personal/{$admin->id}", $this->staff([
            'email' => $admin->email, 'document' => $admin->document, 'role' => Role::Admin->value, 'active' => '0', 'password' => '', 'password_confirmation' => '',
        ]))->assertSessionHasErrors('active');
        $this->assertTrue($admin->fresh()?->active);
    }

    public function test_insurers_are_stored_with_a_verified_nit(): void
    {
        $this->actingAs($this->admin());

        $this->post('/admin/aseguradoras', ['name' => 'Salud Total de Prueba', 'nit' => '900.456.781', 'active' => '1'])->assertRedirect('/admin/aseguradoras');
        $this->post('/admin/aseguradoras', ['name' => 'NIT malo', 'nit' => '900456781-0', 'active' => '1'])
            ->assertSessionHasErrors(['nit' => 'El NIT no es válido. Revisa el número y el dígito de verificación.']);
        $this->post('/admin/aseguradoras', ['name' => 'Duplicada', 'nit' => '900456781', 'active' => '1'])->assertSessionHasErrors('nit');

        $insurer = Insurer::query()->sole();
        $this->assertSame('900456781-6', $insurer->nit);
        $this->assertSame('900.456.781-6', $insurer->formattedNit());
    }
}
