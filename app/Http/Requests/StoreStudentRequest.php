<?php

namespace App\Http\Requests;

use App\Support\ArabicDigits;
use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $enrollment = trim(ArabicDigits::toWestern((string) $this->input('enrollment_number', '')));
        $this->merge([
            'enrollment_number' => $enrollment !== '' ? $enrollment : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'enrollment_number' => ['nullable', 'regex:/^\d+$/', 'max:50'],
            'exam_number' => ['required', 'string', 'max:255'],
            'name_student' => ['required', 'string', 'max:255'],
            'name_father' => ['required', 'string', 'max:255'],
            'name_grandfather' => ['nullable', 'string', 'max:255'],
            'name_surname' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date'],
            'birth_place' => ['required', 'string', 'max:255'],
            'mother_full_name' => ['nullable', 'string', 'max:255'],
            'gender' => ['required', 'string', 'in:ذكر,أنثى,انثى'],
            'branch' => ['required', 'string', 'max:255'],
            'major' => ['required', 'string', 'max:255'],
            'academic_year' => ['required', 'string', 'max:255'],
            'last_school' => ['nullable', 'string', 'max:500'],
            'middle_doc_number' => ['nullable', 'string', 'max:255'],
            'middle_doc_date' => ['nullable', 'date'],
            'issuing_authority' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * رسائل التحقق المخصصة.
     */
    public function messages(): array
    {
        return [
            'exam_number.required' => 'الرقم الامتحاني مطلوب.',
            'enrollment_number.regex' => 'رقم القيد يجب أن يكون رقماً.',
            'enrollment_number.max' => 'رقم القيد طويل جداً.',
        ];
    }

    /**
     * بيانات الطالب الجاهزة لـ CreateStudentCommandHandler (قيم nullable كـ null).
     *
     * @return array<string, string|null>
     */
    public function dataForCreate(): array
    {
        $v = $this->validated();
        $out = [];
        foreach ([
            'enrollment_number', 'exam_number', 'name_student', 'name_father', 'name_grandfather', 'name_surname',
            'birth_date', 'birth_place', 'mother_full_name', 'gender', 'branch', 'major',
            'academic_year', 'last_school', 'middle_doc_number', 'middle_doc_date', 'issuing_authority',
        ] as $key) {
            $val = $v[$key] ?? null;
            $out[$key] = $val !== null && $val !== '' ? trim((string) $val) : null;
        }
        $out['gender'] = $out['gender'] ?? null;
        if ($out['gender'] === 'انثى') {
            $out['gender'] = 'أنثى';
        }

        return $out;
    }
}
