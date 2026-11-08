<?php

namespace App\Http\Requests\Content;

use App\Support\Lookups;
use Illuminate\Foundation\Http\FormRequest;

class IndexNewsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'category' => ['nullable', Lookups::rule('news_categories')],
        ];
    }
}
