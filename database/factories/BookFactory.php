<?php

namespace Database\Factories;

use App\Models\Author;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BookFactory extends Factory
{
    public function definition(): array {
        return [
            // 'uuid' => (string) Str::uuid(),
            'uuid' => $this->faker->uuid(),
            'author_id' => Author::inRandomOrder()->value('id') ?? Author::factory(),
            'title' => $this->faker->unique()->sentence(3),
            'isbn' => $this->faker->unique()->isbn13(),
            'publisher' => $this->faker->company(),
            'publication_year' => $this->faker->numberBetween(1970, 2025),
            'status' => $this->faker->randomElement(['available','rented','reserved']),
            'store_location' => $this->faker->randomElement(['Kuta','Denpasar','Jimbaran','Ubud']),
            'ratings_count' => 0,
            'ratings_avg'   => 0,
        ];
    }
}
