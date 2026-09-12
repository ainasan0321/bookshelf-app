<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'isbn' => ['nullable', 'numeric', 'digits:13', Rule::unique('books', 'isbn')],
            'published_date' => ['nullable','date'],
            'description' => ['nullable', 'string', 'max:100'],
            'image_url' => ['nullable', 'string', 'url', 'max:100'],

            'genres' => ['required', 'array'],
            'genres.*' => ['integer', 'exists:genres,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'タイトルを入力してください。',
            'title.string' => 'タイトルは正しい文字列で入力してください。',
            'title.max' => 'タイトルは255文字以内で入力してください。',
            'author.required' => '著者名を入力してください。',
            'author.string' => '著者名は正しい文字列で入力してください。',
            'author.max' => '著者名は255文字以内で入力してください。',
            'isbn.numeric' => 'ISBNは半角数字で入力してください。',
            'isbn.digits' => 'ISBNは13桁の数字で入力してください。',
            'isbn.unique' => 'このISBNは既に登録されています。',
            'published_date.date' => '出版日は正しい日付で入力してください。',
            'description.string' => '説明は正しい文字列で入力してください。',
            'description.max' => '説明は100文字以内で入力してください。',
            'image_url.string' => '画像のURLは正しい文字列で入力してください。',
            'image_url.url' => '画像のURLは「http://」または「https://」から始まる正しいURL形式で入力してください。',
            'image_url.max' => '画像のURLは100文字以内で入力してください。',
            'genres.required' => 'ジャンルを1つ以上選択してください。',
            'genres.array' => 'ジャンルの選択形式が不正です。',
            'genres.*.integer' => '選択されたジャンルIDが不正です。',
            'genres.*.exists' => '指定されたジャンルIDは存在しません。',
        ];
    }
}
