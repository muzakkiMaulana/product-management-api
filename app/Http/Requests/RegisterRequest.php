<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class RegisterRequest extends FormRequest
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
            'username' => ['bail', 'required', 'string', 'min:3', 'max:50', 'regex:/\A[a-z0-9_-]+\z/', 'unique:users,username'],
            'password' => ['bail', 'required', 'string', 'min:8', 'regex:/\A[^\x00]{1,72}\z/', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return ['password.regex' => 'The password must be at most 72 bytes and contain no null bytes.'];
    }
}
