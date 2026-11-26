<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StaffRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

final class StaffController extends Controller
{
    public function index(): View
    {
        return view('admin.staff.index', [
            'staff' => User::query()->orderByDesc('active')->orderBy('role')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.staff.form', ['member' => new User(['role' => Role::Therapist, 'active' => true])]);
    }

    public function store(StaffRequest $request): RedirectResponse
    {
        User::query()->create($request->staff());

        return redirect()->route('admin.staff.index')->with('status', 'Cuenta creada. Entrégale la contraseña de forma segura.');
    }

    public function edit(User $staff): View
    {
        return view('admin.staff.form', ['member' => $staff]);
    }

    public function update(StaffRequest $request, User $staff): RedirectResponse
    {
        $data = $request->staff();
        if ($staff->is($request->user()) && ($data['active'] === false || $data['role'] !== Role::Admin->value)) {
            throw ValidationException::withMessages(['active' => 'No puedes desactivar tu propia cuenta ni quitarte el rol administrativo.']);
        }
        $staff->update($data);

        return redirect()->route('admin.staff.index')->with('status', 'Cuenta de '.$staff->fullName().' actualizada.');
    }
}
