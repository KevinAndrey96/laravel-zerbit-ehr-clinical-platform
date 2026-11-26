<?php

namespace App\Http\Requests\Clinical;

use App\Domain\Signature\SignatureImage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use InvalidArgumentException;

/**
 * Handwritten signature drawn on the canvas, sent as a PNG data URL.
 */
final class SignatureRequest extends FormRequest
{
    protected $errorBag = 'signature';

    private ?SignatureImage $image = null;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return ['signature' => ['required', 'string', 'max:300000']];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return ['signature.required' => 'Dibuja la firma en el recuadro antes de guardar.'];
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
            try {
                $this->image = SignatureImage::fromDataUrl($this->string('signature')->toString());
            } catch (InvalidArgumentException) {
                $validator->errors()->add('signature', 'La firma no es válida. Bórrala y vuelve a dibujarla.');
            }
        }];
    }

    public function signature(): SignatureImage
    {
        return $this->image ?? SignatureImage::fromDataUrl($this->string('signature')->toString());
    }
}
