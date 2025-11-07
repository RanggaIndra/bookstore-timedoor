<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AuthorFactory extends Factory
{
    public function definition(): array {
        return [
            // 'uuid' => (string) Str::uuid(),
            'uuid' => $this->faker->uuid(),
            'name' => $this->faker->name(),
            'country' => $this->faker->country(),
        ];
    }
}
