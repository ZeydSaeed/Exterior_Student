<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * FormRequest لتحديث بيانات الطالب ودرجاته من مودال الدرجات.
 * النتيجة والدور: لا يُقبل إلا الخيارات المعرّفة في config/grades_catalog.
 */
class UpdateStudentGradesRequest extends FormRequest
{
    public function authorize(): bool
    {
        // يمكن إضافة صلاحيات لاحقاً، حالياً السماح دائماً
        return true;
    }

    public function rules(): array
    {
        return [
            'name_student' => ['nullable', 'string', 'max:255'],
            'name_father' => ['nullable', 'string', 'max:255'],
            'name_grandfather' => ['nullable', 'string', 'max:255'],
            'name_surname' => ['nullable', 'string', 'max:255'],
            'exam_number' => ['required', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'birth_place' => ['nullable', 'string', 'max:500'],
            'mother_full_name' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'max:255'],
            'branch' => ['nullable', 'string', 'max:255'],
            'major' => ['nullable', 'string', 'max:255'],
            'academic_year' => ['nullable', 'string', 'max:255'],
            'last_school' => ['nullable', 'string', 'max:500'],
            'middle_doc_number' => ['nullable', 'string', 'max:255'],
            'middle_doc_date' => ['nullable', 'date'],
            'issuing_authority' => ['nullable', 'string', 'max:500'],
            'result' => ['nullable', 'string', Rule::in(config('grades_catalog.result_options'))],
            'total' => ['nullable', 'string', 'max:255'],
            'average' => ['nullable', 'string', 'max:255'],
            'round' => ['nullable', 'string', Rule::in(config('grades_catalog.round_options', ['الاول', 'الثاني', 'الثالث', 'الاول تكميلي', 'الثاني تكميلي', 'الثالث تكميلي']))],
            'grades' => ['nullable', 'array'],
            'grades.*.subject' => ['nullable', 'string', 'max:255'],
            'grades.*.score' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'exam_number.required' => 'الرقم الامتحاني مطلوب في فورم الدرجات.',
        ];
    }

    /**
     * إرجاع بايلود مُطبّع للاستخدام داخل الـ Command Handler.
     *
     * @return array{
     *   name_student?: string,
     *   name_father?: string,
     *   name_grandfather?: string,
     *   name_surname?: string,
     *   exam_number?: string,
     *   birth_date?: string,
     *   birth_place?: string,
     *   mother_full_name?: string,
     *   gender?: string,
     *   branch?: string,
     *   major?: string,
     *   academic_year?: string,
     *   last_school?: string,
     *   middle_doc_number?: string,
     *   middle_doc_date?: string,
     *   issuing_authority?: string,
     *   result?: string,
     *   total?: string,
     *   average?: string,
     *   round?: string,
     *   grades?: array<int, array{subject?: string, score?: string}>
     * }
     */
    public function normalizedPayload(): array
    {
        // دعم JSON body (fetch PUT) في حال لم تُرسل حقول منفصلة
        $payload = $this->all();
        if (empty($payload) && $this->getContent()) {
            $decoded = json_decode($this->getContent(), true);
            if (is_array($decoded)) {
                $payload = $decoded;
            }
        }

        $allowedKeys = [
            'name_student', 'name_father', 'name_grandfather', 'name_surname',
            'exam_number', 'birth_date', 'birth_place', 'mother_full_name',
            'gender', 'branch', 'major', 'academic_year',
            'last_school', 'middle_doc_number', 'middle_doc_date', 'issuing_authority',
            'result', 'total', 'average', 'round', 'grades',
        ];

        $payload = array_intersect_key(
            $payload,
            array_fill_keys($allowedKeys, true)
        );

        if (isset($payload['grades']) && ! is_array($payload['grades'])) {
            $payload['grades'] = [];
        }

        return $payload;
    }
}
