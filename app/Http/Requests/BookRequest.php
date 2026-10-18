<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'isbn' => strtoupper(preg_replace('/[^0-9Xx]/', '', (string) $this->input('isbn'))),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'isbn' => [
                'required',
                'regex:/^(\d{9}[\dX]|\d{13})$/',
                Rule::unique('books', 'isbn')->ignore($this->route('book')),
            ],
            'publication_year' => ['nullable', 'integer', 'min:-3000', 'max:'.now()->year],
            'category_id' => ['required', 'exists:categories,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'isbn.regex' => 'An ISBN has 10 or 13 digits. Check the number on the back cover.',
            'isbn.unique' => 'This ISBN is already in the catalog.',
        ];
    }
}
