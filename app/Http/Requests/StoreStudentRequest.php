<?php

namespace App\Http\Requests;

use App\Support\AcademicYearOptions;
use App\Support\ArabicDigits;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $enrollment = trim(ArabicDigits::toWestern((string) $this->input('enrollment_number', '')));
        $grades = $this->input('grades', []);
        if (! is_array($grades)) {
            $grades = [];
        }

        $normalizedGrades = [];
        foreach ($grades as $row) {
            if (! is_array($row)) {
                continue;
            }
            $subject = trim((string) ($row['subject'] ?? ''));
            $score = trim(ArabicDigits::toWestern((string) ($row['score'] ?? '')));
            if ($subject === '') {
                continue;
            }
            $normalizedGrades[] = [
                'subject' => $subject,
                'score' => $score,
            ];
        }

        $this->merge([
            'enrollment_number' => $enrollment !== '' ? $enrollment : null,
            'grades' => $normalizedGrades,
        ]);
    }

    public function rules(): array
    {
        return [
            'enrollment_number' => ['nullable', 'regex:/^\d+$/', 'max:50'],
            'exam_number' => ['required', 'string', 'max:255'],
            'name_student' => ['required', 'string', 'max:255'],
            'name_father' => ['required', 'string', 'max:255'],
            'name_grandfather' => ['required', 'string', 'max:255'],
            'name_surname' => ['required', 'string', 'max:255'],
            'birth_date' => ['required', 'date'],
            'birth_place' => ['required', 'string', 'max:255'],
            'mother_full_name' => ['nullable', 'string', 'max:255'],
            'gender' => ['required', 'string', 'in:ذكر,أنثى,انثى'],
            'branch' => ['required', 'string', 'max:255'],
            'major' => ['required', 'string', 'max:255'],
            'academic_year' => ['required', 'string', Rule::in(AcademicYearOptions::all())],
            'last_school' => ['nullable', 'string', 'max:500'],
            'middle_doc_number' => ['nullable', 'string', 'max:255'],
            'middle_doc_date' => ['nullable', 'date'],
            'issuing_authority' => ['nullable', 'string', 'max:500'],
            'grades' => ['nullable', 'array'],
            'grades.*.subject' => ['required', 'string', 'max:255'],
            'grades.*.score' => ['nullable', 'string', 'max:255'],
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
            'academic_year.required' => 'العام الدراسي مطلوب.',
            'academic_year.in' => 'العام الدراسي غير صالح.',
            'name_surname.required' => 'اسم اب الجد مطلوب.',
            'name_grandfather.required' => 'اسم الجد مطلوب.',
        ];
    }

    /**
     * بيانات الطالب الجاهزة لـ CreateStudentCommandHandler.
     *
     * @return array<string, mixed>
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

        $grades = [];
        foreach ($v['grades'] ?? [] as $row) {
            if (! is_array($row)) {
                continue;
            }
            $subject = trim((string) ($row['subject'] ?? ''));
            if ($subject === '') {
                continue;
            }
            $grades[] = [
                'subject' => $subject,
                'score' => trim((string) ($row['score'] ?? '')),
            ];
        }
        $out['grades'] = $grades;

        return $out;
    }
}
