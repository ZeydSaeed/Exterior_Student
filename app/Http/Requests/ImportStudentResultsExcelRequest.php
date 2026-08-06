<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ImportStudentResultsExcelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
            'round' => ['required', 'string', Rule::in(config('grades_catalog.round_options', []))],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'يرجى اختيار ملف Excel.',
            'file.mimes' => 'الملف يجب أن يكون بصيغة Excel (xlsx أو xls).',
            'file.max' => 'حجم الملف يجب ألا يتجاوز 10 ميجابايت.',
            'round.required' => 'يرجى اختيار الدور.',
            'round.in' => 'قيمة الدور غير صالحة.',
        ];
    }
}
