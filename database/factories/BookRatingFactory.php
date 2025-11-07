<?php

namespace Database\Factories;

use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookRatingFactory extends Factory
{
    public function definition(): array {
        $fp = sha1($this->faker->unique()->safeEmail());
        return [
            'book_id' => Book::inRandomOrder()->value('id') ?? Book::factory(),
            'score' => $this->faker->numberBetween(1,10),
            // 'rater_fingerprint' => $fp,
            'rater_fingerprint' => sha1($this->faker->ipv4().'|'.$this->faker->userAgent()),
            // 'created_at' => $this->faker->dateTimeBetween('-180 days','now'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
