<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    public function definition(): array {
        return [
            // 'uuid' => (string) Str::uuid(),
            'uuid' => $this->faker->uuid(),
            'name' => ucfirst($this->faker->unique()->words(2, true)),
        ];
    }
}
