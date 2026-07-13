<?php

namespace App\Http\Requests;

use App\Support\ImportDateNormalizer;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $raw = trim((string) $this->input('document_date', ''));
        if ($raw === '') {
            $this->merge(['document_date' => null]);

            return;
        }

        $ymd = ImportDateNormalizer::toYmd($raw);
        $this->merge(['document_date' => $ymd ?? $raw]);
    }

    public function rules(): array
    {
        return [
            'document_number' => ['nullable', 'string', 'max:255'],
            'document_date' => ['nullable', 'date'],
            'addressee' => ['nullable', 'string', 'max:255'],
            'purpose' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'document_date.date' => 'تاريخ الوثيقة غير صالح. استخدم يوم / شهر / سنة مثل 15 / 06 / 2006.',
            'document_number.max' => 'رقم الوثيقة طويل جداً.',
            'addressee.max' => 'حقل الجهة المعنونة إليها طويل جداً.',
            'purpose.max' => 'حقل الغرض من الوثيقة طويل جداً.',
        ];
    }
}
