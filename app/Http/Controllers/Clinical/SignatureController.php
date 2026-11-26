<?php

namespace App\Http\Controllers\Clinical;

use App\Http\Controllers\Controller;
use App\Http\Requests\Clinical\SignatureRequest;
use App\Support\PrivateFiles;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * The therapist's own signature, printed on the clinical history PDF.
 */
final class SignatureController extends Controller
{
    public function __construct(private readonly PrivateFiles $files) {}

    public function edit(Request $request): View
    {
        return view('clinical.signature', ['current' => $this->files->dataUrl($this->currentUser($request)->signature_path)]);
    }

    public function update(SignatureRequest $request): RedirectResponse
    {
        $user = $this->currentUser($request);
        $previous = $user->signature_path;
        $user->forceFill(['signature_path' => $this->files->storeSignature($request->signature())])->save();
        $this->files->delete($previous);

        return redirect()->route('signature.edit')->with('status', 'Tu firma quedó guardada. Aparecerá en las historias clínicas que imprimas.');
    }
}
