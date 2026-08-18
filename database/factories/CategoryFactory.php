<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->word(); // Generates a clean single word like "Technology" or "Programming"

        return [
            'name' => ucfirst($name), // Capitalizes the first letter
            'user_id' => User::factory()->author()->create(),
            'slug' => Str::slug($name), // Converts it into a clean, matching URL slug: "technology" or "programming"
        ];
    }
}
