<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'        => User::factory(),
            'title'          => fake()->sentence(3),
            'author'         => fake()->name(),
            'isbn'           => null,
            'published_date' => null,
            'description'    => fake()->paragraph(),
            'image_url'      => null,
        ];
    }
}
