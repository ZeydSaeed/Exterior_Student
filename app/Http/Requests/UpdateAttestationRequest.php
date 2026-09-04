<?php

namespace App\Http\Requests;

use App\Support\ArabicDigits;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAttestationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $number = trim(ArabicDigits::toWestern((string) $this->input('number', '')));
        $this->merge([
            'number' => $number !== '' ? $number : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'date' => ['nullable', 'date'],
            'number' => ['nullable', 'string', 'max:255'],
            'issued_to' => ['nullable', 'string', 'max:255'],
            'right_title' => ['nullable', 'string', 'max:255'],
            'right_employee_name' => ['nullable', 'string', 'max:255'],
            'left_title' => ['nullable', 'string', 'max:255'],
            'left_employee_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
