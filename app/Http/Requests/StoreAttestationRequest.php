<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttestationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:with_grades,without_grades'],
            'date' => ['nullable', 'date'],
            'number' => ['nullable', 'string', 'max:255'],
            'issued_to' => ['nullable', 'string', 'max:255'],
            'right_title' => ['nullable', 'string', 'max:255'],
            'right_employee_name' => ['nullable', 'string', 'max:255'],
            'left_title' => ['nullable', 'string', 'max:255'],
            'left_employee_name' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * تحويل القيم الفارغة إلى null لتفادي فشل تحقق التاريخ ولضمان وصول number و issued_to.
     */
    protected function prepareForValidation(): void
    {
        $merge = [];
        foreach (['date', 'number', 'issued_to', 'right_title', 'right_employee_name', 'left_title', 'left_employee_name'] as $key) {
            if ($this->has($key) && $this->input($key) === '') {
                $merge[$key] = null;
            }
        }
        if ($merge !== []) {
            $this->merge($merge);
        }
    }
}
