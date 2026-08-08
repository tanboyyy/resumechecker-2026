<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
            ],
            'email' => [
                'sometimes',
                'email',
                'max:255',
                'unique:users,email,' . $userId,
            ],
            'avatar' => [
                'sometimes',
                'nullable',
                'string',
                'max:512',
                'url',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.max' => 'The name must not exceed 255 characters.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already in use.',
            'email.max' => 'The email must not exceed 255 characters.',
            'avatar.url' => 'The avatar URL must be a valid URL.',
            'avatar.max' => 'The avatar URL must not exceed 512 characters.',
        ];
    }
}
