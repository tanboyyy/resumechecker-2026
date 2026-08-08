<?php

namespace App\Http\Requests\Resume;

use Illuminate\Foundation\Http\FormRequest;

class StoreResumeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:20480',
                'mimes:pdf,docx',
            ],
            'title' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Please upload a resume file.',
            'file.max' => 'The resume file must not be larger than 20MB.',
            'file.mimes' => 'The resume must be a PDF or DOCX file.',
            'title.max' => 'The title must not exceed 255 characters.',
        ];
    }
}
