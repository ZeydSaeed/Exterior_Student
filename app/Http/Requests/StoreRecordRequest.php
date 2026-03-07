<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRecordRequest extends FormRequest
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
}
