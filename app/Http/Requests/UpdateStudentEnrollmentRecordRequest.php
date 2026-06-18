<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentEnrollmentRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page_number' => ['nullable', 'string', 'max:50'],
            'enrollment_number' => ['nullable', 'string', 'max:50'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'page_number.max' => 'رقم الصفحة طويل جداً.',
            'enrollment_number.max' => 'رقم القيد طويل جداً.',
        ];
    }
}
