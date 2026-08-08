<?php

namespace App\Http\Requests\Analysis;

use Illuminate\Foundation\Http\FormRequest;

class CreateAnalysisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'type' => [
                'required',
                'string',
                'in:ats,content,formatting,comparison',
            ],
        ];

        if ($this->input('type') === 'comparison') {
            $rules['job_description'] = [
                'required',
                'string',
                'min:50',
                'max:10000',
            ];
        } else {
            $rules['job_description'] = [
                'nullable',
                'string',
                'max:10000',
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Please select an analysis type.',
            'type.in' => 'The analysis type must be general, comparison, rewrite, or interview.',
            'job_description.required' => 'A job description is required for comparison analysis.',
            'job_description.min' => 'The job description must be at least 50 characters.',
            'job_description.max' => 'The job description must not exceed 10,000 characters.',
        ];
    }
}
