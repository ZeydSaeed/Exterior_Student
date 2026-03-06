<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveCertificateSignatureSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $right = $this->input('right_signature');
        $left = $this->input('left_signature');
        $this->merge([
            'right_signature' => ($right !== null && $right !== '') ? $right : null,
            'left_signature' => ($left !== null && $left !== '') ? $left : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'right_signature' => ['nullable', 'integer', 'exists:employees,id'],
            'left_signature' => ['nullable', 'integer', 'exists:employees,id'],
        ];
    }
}
