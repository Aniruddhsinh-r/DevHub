<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\User;
use App\Models\View;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<View>
 */
class ViewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'article_id' => Article::factory(),
            'viewed' => 1,
            'created_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
