<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use App\Enums\ArticleStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(15);
        $status = ArticleStatus::PUBLISHED;
        return [
            'user_id' => User::factory()->author()->create(),
            'category_id' => Category::factory(),
            'title' => ($title),
            'slug' => Str::slug($title),
            'excerpt' => fake()->paragraph(1),
            'body' => fake()->paragraphs(6, true),
            'status' => $status,
            'published_at' => $status === ArticleStatus::PUBLISHED ? fake()->dateTimeBetween('-1 month', 'now') : null,
            'created_at' => now(),
        ];
    }
}
