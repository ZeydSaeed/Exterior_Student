<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class ImportDatabaseBackupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'pick_path' => ['sometimes', 'boolean'],
            'file' => [
                'required_without:pick_path',
                'file',
                'max:131072',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! $value instanceof UploadedFile) {
                        return;
                    }

                    $extension = strtolower((string) $value->getClientOriginalExtension());
                    if ($extension !== 'esbak') {
                        $fail('يجب أن يكون الملف نسخة احتياطية مشفّرة بصيغة .esbak.');
                    }
                },
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required' => 'يرجى اختيار ملف قاعدة البيانات.',
            'file.required_without' => 'يرجى اختيار ملف قاعدة البيانات.',
            'file.file' => 'يرجى اختيار ملف صالح.',
            'file.uploaded' => 'تعذر رفع الملف. استخدم نافذة اختيار الملف أو تأكد أن حجمه لا يتجاوز حد الرفع.',
            'file.max' => 'حجم الملف يجب ألا يتجاوز 128 ميجابايت.',
        ];
    }
}
