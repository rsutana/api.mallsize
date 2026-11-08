<?php

namespace App\Http\Requests\NewsPost;

use App\Support\Lookups;
use Illuminate\Foundation\Http\FormRequest;

class SaveNewsPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isStaff();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', Lookups::rule('news_categories')],
            'summary' => ['nullable', 'string', 'max:500'],
            'body' => ['nullable', 'string', 'max:50000'],
            'status' => ['required', Lookups::rule('post_statuses')],
            'published_on' => ['required', 'date'],
            'is_featured' => ['sometimes', 'boolean'],
            'cover' => ['nullable', 'image', 'max:4096'],
            'is_cover_removed' => ['sometimes', 'boolean'],
        ];
    }
}
