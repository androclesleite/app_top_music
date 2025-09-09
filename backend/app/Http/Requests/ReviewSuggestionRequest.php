<?php

namespace App\Http\Requests;

use App\Models\SongSuggestion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewSuggestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                Rule::in(['approve', 'reject'])
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'A ação é obrigatória.',
            'status.in' => 'A ação deve ser "approve" ou "reject".',
        ];
    }
}
