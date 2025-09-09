<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSongRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $songId = $this->route('song')?->id ?? $this->route('song');

        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'youtube_url' => [
                'sometimes',
                'required',
                'url',
                'regex:/^https?:\/\/(www\.)?(youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/',
                Rule::unique('songs', 'youtube_url')->ignore($songId),
                Rule::unique('song_suggestions', 'youtube_url')
            ],
            'position' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'O título é obrigatório.',
            'title.max' => 'O título deve ter no máximo 255 caracteres.',
            'youtube_url.required' => 'A URL do YouTube é obrigatória.',
            'youtube_url.url' => 'A URL do YouTube deve ser válida.',
            'youtube_url.regex' => 'A URL deve ser um link válido do YouTube.',
            'youtube_url.unique' => 'Esta URL do YouTube já está cadastrada.',
            'position.integer' => 'A posição deve ser um número inteiro.',
            'position.min' => 'A posição deve ser no mínimo 1.',
            'position.max' => 'A posição deve ser no máximo 1000.',
        ];
    }
}