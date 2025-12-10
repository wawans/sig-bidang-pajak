<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:225', 'min:3'],
            'email' => ['required', 'email', 'max:225'],
            'password' => ['sometimes', 'nullable', 'string', 'confirmed', 'min:6', 'max:225'],
            'status_aktif' => ['sometimes', 'nullable', 'boolean'],
            'status_email_verified' => ['sometimes', 'nullable', 'boolean'],
            'roles' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
