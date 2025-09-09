<?php

namespace App\Http\Requests;

use App\Helpers\YouTubeHelper;
use Illuminate\Foundation\Http\FormRequest;

class StoreSuggestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'artist' => ['nullable', 'string', 'max:255'],
            'youtube_url' => [
                'required',
                'url',
                function ($attribute, $value, $fail) {
                    if (!YouTubeHelper::isValidUrl($value)) {
                        $fail('A URL deve ser um link válido do YouTube.');
                    }
                },
                'unique:songs,youtube_url',
                'unique:song_suggestions,youtube_url'
            ],
            'suggested_by' => ['nullable', 'string', 'max:255'],
            'suggested_by_name' => ['nullable', 'string', 'max:255'],
            'suggested_by_email' => ['nullable', 'email', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'O título é obrigatório.',
            'title.max' => 'O título deve ter no máximo 255 caracteres.',
            'artist.max' => 'O nome do artista deve ter no máximo 255 caracteres.',
            'youtube_url.required' => 'A URL do YouTube é obrigatória.',
            'youtube_url.url' => 'A URL do YouTube deve ser válida.',
            'youtube_url.unique' => 'Esta URL do YouTube já está cadastrada.',
            'suggested_by.max' => 'O nome deve ter no máximo 255 caracteres.',
            'suggested_by_name.max' => 'O nome deve ter no máximo 255 caracteres.',
            'suggested_by_email.email' => 'O e-mail deve ser um endereço válido.',
            'suggested_by_email.max' => 'O e-mail deve ter no máximo 255 caracteres.',
        ];
    }
}