<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
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
            'parent_id' => null, // Default is a top-level parent comment
            'body' => fake()->paragraph(fake()->numberBetween(1, 2)),
            'edited_at' => null,
            'created_at' => fake()->dateTimeBetween('-1 week', 'now'),
        ];
    }

    /**
     * State modifier for generating nested replies smoothly
     */
    public function reply(Comment $parentComment): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parentComment->id,
            'article_id' => $parentComment->article_id, // Keeps them on the same article stream
            'created_at' => fake()->dateTimeBetween($parentComment->created_at, 'now'),
        ]);
    }
}
