<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'          => ['required', 'string', 'max:255'],
            'author'         => ['required', 'string', 'max:255'],
            'isbn'           => ['nullable', 'digits:13', 'unique:books,isbn'],
            'published_date' => ['nullable', 'date'],
            'description'    => ['nullable', 'string'],
            'image_url'      => ['nullable', 'url'],
            'genres'         => ['required', 'array', 'min:1'],
            'genres.*'       => ['integer', 'exists:genres,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'          => 'タイトルは必須です。',
            'title.max'               => 'タイトルは255文字以内で入力してください。',
            'author.required'         => '著者名は必須です。',
            'author.max'              => '著者名は255文字以内で入力してください。',
            'isbn.required'           => 'ISBNは必須です。',
            'isbn.digits'             => 'ISBNは13桁の数字で入力してください。',
            'isbn.unique'             => 'このISBNはすでに登録されています。',
            'published_date.required' => '出版日は必須です。',
            'published_date.date'     => '出版日は有効な日付で入力してください。',
            'image_url.url'           => '画像URLは正しいURL形式で入力してください。',
            'genres.required'         => 'ジャンルを1つ以上指定してください。',
            'genres.min'              => 'ジャンルを1つ以上指定してください。',
            'genres.*.exists'         => '指定されたジャンルが存在しません。',
        ];
    }
}
