<?php

namespace Database\Factories;

use App\Models\Lookups\NewsCategory;
use App\Models\Lookups\PostStatus;
use App\Models\NewsPost;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NewsPost>
 */
class NewsPostFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(5),
            'category' => fake()->randomElement([NewsCategory::ANNOUNCEMENT, NewsCategory::ENROLLMENT, NewsCategory::EVENT]),
            'summary' => fake()->sentence(),
            'body' => fake()->paragraphs(3, true),
            'status' => PostStatus::PUBLISHED,
            'published_on' => now()->subDay()->toDateString(),
            'is_featured' => false,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => ['status' => PostStatus::DRAFT]);
    }
}
