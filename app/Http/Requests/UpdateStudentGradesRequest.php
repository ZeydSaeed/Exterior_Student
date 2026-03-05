<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FormRequest لتحديث بيانات الطالب ودرجاته من مودال الدرجات.
 * مسؤولة عن التحقق البسيط + تطبيع البايلود للـ Command Handler.
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
            'name_student'      => ['nullable', 'string', 'max:255'],
            'name_father'       => ['nullable', 'string', 'max:255'],
            'name_grandfather'  => ['nullable', 'string', 'max:255'],
            'name_surname'      => ['nullable', 'string', 'max:255'],
            'exam_number'       => ['nullable', 'string', 'max:255'],
            'gender'            => ['nullable', 'string', 'max:255'],
            'branch'            => ['nullable', 'string', 'max:255'],
            'major'             => ['nullable', 'string', 'max:255'],
            'academic_year'     => ['nullable', 'string', 'max:255'],
            'result'            => ['nullable', 'string', 'max:255'],
            'total'             => ['nullable', 'string', 'max:255'],
            'average'           => ['nullable', 'string', 'max:255'],
            'round'             => ['nullable', 'string', 'max:255'],
            'grades'            => ['nullable', 'array'],
            'grades.*.subject'  => ['nullable', 'string', 'max:255'],
            'grades.*.score'    => ['nullable', 'string', 'max:255'],
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
     *   gender?: string,
     *   branch?: string,
     *   major?: string,
     *   academic_year?: string,
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
            'exam_number', 'gender', 'branch', 'major', 'academic_year',
            'result', 'total', 'average', 'round', 'grades',
        ];

        $payload = array_intersect_key(
            $payload,
            array_fill_keys($allowedKeys, true)
        );

        if (isset($payload['grades']) && !is_array($payload['grades'])) {
            $payload['grades'] = [];
        }

        return $payload;
    }
}

