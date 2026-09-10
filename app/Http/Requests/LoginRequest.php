<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('username'))) {
            $this->merge(['username' => Str::lower($this->input('username'))]);
        }
    }

    public function rules(): array
    {
        return [
            'username' => ['bail', 'required', 'string', 'max:50'],
            'password' => ['bail', 'required', 'string', 'regex:/\A[^\x00]{1,72}\z/'],
        ];
    }

    public function messages(): array
    {
        return ['password.regex' => 'The password must be at most 72 bytes and contain no null bytes.'];
    }
}
