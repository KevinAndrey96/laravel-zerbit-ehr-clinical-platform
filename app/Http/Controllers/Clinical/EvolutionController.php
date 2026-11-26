<?php

namespace App\Http\Controllers\Clinical;

use App\Actions\RecordEvolution;
use App\Actions\SignEvolution;
use App\Http\Controllers\Controller;
use App\Http\Requests\Clinical\EvolutionRequest;
use App\Http\Requests\Clinical\SignatureRequest;
use App\Models\ClinicalHistory;
use App\Models\Evolution;
use Illuminate\Http\RedirectResponse;

/**
 * Session notes are append-only: there are no edit or delete routes, a
 * correction is written as a new note.
 */
final class EvolutionController extends Controller
{
    public function store(EvolutionRequest $request, ClinicalHistory $history, RecordEvolution $record): RedirectResponse
    {
        $evolution = $record->handle($history, $this->currentUser($request), $request->validated());

        return redirect()->to(route('histories.show', $history).'#sesion-'.$evolution->session_number)
            ->with('status', 'Sesión '.$evolution->session_number.' registrada. Pide al paciente que la firme.');
    }

    public function sign(SignatureRequest $request, ClinicalHistory $history, Evolution $evolution, SignEvolution $sign): RedirectResponse
    {
        $sign->handle($evolution, $this->currentUser($request), $request->signature());

        return redirect()->to(route('histories.show', $history).'#sesion-'.$evolution->session_number)
            ->with('status', 'Firma del paciente guardada en la sesión '.$evolution->session_number.'.');
    }
}
