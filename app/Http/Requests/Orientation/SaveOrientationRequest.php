<?php

namespace App\Http\Requests\Orientation;

use App\Models\Lookups\PostStatus;
use App\Support\Lookups;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveOrientationRequest extends FormRequest
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
            'course_id' => ['nullable', 'integer', Rule::exists('courses', 'id')],
            'details' => ['nullable', 'string', 'max:5000'],
            'date' => ['required', 'date'],
            'time_label' => ['nullable', 'string', 'max:60'],
            'format' => ['required', Lookups::rule('orientation_formats')],
            'venue' => ['nullable', 'string', 'max:255'],
            'is_social_linked' => ['required', 'boolean'],
            'social_platform' => ['nullable', 'required_if_accepted:is_social_linked', Lookups::rule('social_platforms')],
            'social_url' => ['nullable', 'required_if_accepted:is_social_linked', 'url:https,http', 'max:500', $this->socialUrlRule()],
            'link_mode' => ['required', Lookups::rule('orientation_link_modes')],
            'status' => ['required', Rule::in([PostStatus::DRAFT, PostStatus::PUBLISHED]), Lookups::rule('post_statuses')],
            'is_notify_students' => ['required', 'boolean'],
        ];
    }

    /**
     * The URL must belong to the selected platform.
     */
    private function socialUrlRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (! $this->boolean('is_social_linked') || ! is_string($value) || $value === '') {
                return;
            }

            $domains = [
                'facebook' => '/(^|\.)(facebook\.com|fb\.com|fb\.watch)$/i',
                'instagram' => '/(^|\.)instagram\.com$/i',
                'tiktok' => '/(^|\.)tiktok\.com$/i',
                'youtube' => '/(^|\.)(youtube\.com|youtu\.be)$/i',
            ];

            $pattern = $domains[(string) $this->input('social_platform')] ?? null;
            $host = (string) parse_url($value, PHP_URL_HOST);

            if ($pattern !== null && ! preg_match($pattern, $host)) {
                $fail('This doesn’t look like a '.Lookups::label('social_platforms', (string) $this->input('social_platform')).' link.');
            }
        };
    }
}
