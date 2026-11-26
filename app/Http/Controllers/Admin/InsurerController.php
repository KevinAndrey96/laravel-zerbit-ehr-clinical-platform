<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InsurerRequest;
use App\Models\Insurer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

final class InsurerController extends Controller
{
    public function index(): View
    {
        return view('admin.insurers.index', [
            'insurers' => Insurer::query()->withCount('patients')->orderByDesc('active')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.insurers.form', ['insurer' => new Insurer(['active' => true])]);
    }

    public function store(InsurerRequest $request): RedirectResponse
    {
        Insurer::query()->create($request->insurer());

        return redirect()->route('admin.insurers.index')->with('status', 'Aseguradora registrada.');
    }

    public function edit(Insurer $insurer): View
    {
        return view('admin.insurers.form', ['insurer' => $insurer]);
    }

    public function update(InsurerRequest $request, Insurer $insurer): RedirectResponse
    {
        $insurer->update($request->insurer());

        return redirect()->route('admin.insurers.index')->with('status', 'Aseguradora actualizada.');
    }
}
