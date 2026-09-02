<?php

namespace Database\Factories;

use App\Models\Author;
use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(4)),
            'author_id' => Author::factory(),
            'publisher_id' => null,
            'description' => fake()->paragraphs(2, true),
            'content' => fake()->paragraphs(8, true),
            'cover_color' => fake()->randomElement(['#274A66', '#4E7291', '#A8792F', '#566B80', '#4F7386', '#43596E']),
            'pages' => fake()->numberBetween(80, 400),
            'year' => fake()->numberBetween(2015, 2025),
            'language' => 'id',
            'views' => fake()->numberBetween(50, 5000),
            'rating_avg' => 0,
            'rating_count' => 0,
            'is_featured' => false,
            'is_published' => true,
        ];
    }
}
