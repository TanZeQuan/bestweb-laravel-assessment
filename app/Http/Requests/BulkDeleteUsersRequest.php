<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkDeleteUsersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'user_ids' => ['required', 'array', 'min:1', 'max:100'],
            'user_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('users', 'id')->whereNull('deleted_at'),
            ],
        ];
    }
}
