<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class BookIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'keyword'  => ['nullable', 'string', 'max:255'],
            'genre'    => ['nullable', 'integer', 'exists:genres,id'],
            'page'     => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'genre.exists'      => '指定されたジャンルが存在しません。',
            'page.integer'      => 'ページ番号は整数で指定してください。',
            'page.min'          => 'ページ番号は1以上で指定してください。',
            'per_page.integer'  => '件数は整数で指定してください。',
            'per_page.min'      => '件数は1以上で指定してください。',
            'per_page.max'      => '件数は100以下で指定してください。',
        ];
    }
}
