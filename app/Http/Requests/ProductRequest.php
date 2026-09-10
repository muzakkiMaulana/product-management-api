<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'price' => ['bail', 'required', 'numeric', 'decimal:0,2', 'between:0,9999999999.99'],
            'description' => ['nullable', 'string', 'max:10000'],
            'category' => ['required', 'string', 'max:255'],
            'images' => ['required', 'array', 'list', 'min:1'],
            'images.*' => ['bail', 'required', 'string', 'max:2048', 'url:http,https'],
        ];
    }
}
