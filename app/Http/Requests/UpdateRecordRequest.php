<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
            'document_date.date' => 'تاريخ الوثيقة غير صالح.',
            'document_number.max' => 'رقم الوثيقة طويل جداً.',
            'addressee.max' => 'حقل الجهة المعنونة إليها طويل جداً.',
            'purpose.max' => 'حقل الغرض من الوثيقة طويل جداً.',
        ];
    }
}
