<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user),
            ],
            'phone_number' => [
                'sometimes',
                'required',
                'string',
                'max:30',
                Rule::unique('users', 'phone_number')->ignore($user),
            ],
            'password' => ['sometimes', 'required', 'string', 'min:8'],
            'status' => ['sometimes', 'required', 'in:active,inactive'],
        ];
    }
}
