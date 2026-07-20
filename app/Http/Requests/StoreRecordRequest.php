<?php

namespace App\Http\Requests;

use App\Support\ArabicDigits;
use App\Support\ImportDateNormalizer;
use Illuminate\Foundation\Http\FormRequest;

class StoreRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $number = trim(ArabicDigits::toWestern((string) $this->input('document_number', '')));
        $this->merge([
            'document_number' => $number !== '' ? $number : null,
        ]);

        $raw = trim(ArabicDigits::toWestern((string) $this->input('document_date', '')));
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
            'document_number' => ['required', 'string', 'max:255'],
            'document_date' => ['nullable', 'date'],
            'addressee' => ['nullable', 'string', 'max:2000'],
            'purpose' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'document_number.required' => 'أدخل رقم الوثيقة.',
            'document_date.date' => 'تاريخ الوثيقة غير صالح. استخدم يوم / شهر / سنة مثل 15 / 06 / 2006.',
            'document_number.max' => 'رقم الوثيقة طويل جداً.',
            'addressee.max' => 'حقل الجهة المعنونة إليها طويل جداً (الحد الأقصى 2000 حرف).',
            'purpose.max' => 'حقل الغرض من الوثيقة طويل جداً.',
        ];
    }
}
