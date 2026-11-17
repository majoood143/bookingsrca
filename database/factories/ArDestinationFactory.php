<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ArDestinationFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->city();

        return [
            'slug' => Str::slug($title).'-'.Str::random(4),
            'title' => ['en' => $title, 'ar' => $title],
            'summary' => ['en' => fake()->sentence(), 'ar' => fake()->sentence()],
            'description' => ['en' => fake()->paragraph(), 'ar' => fake()->paragraph()],
            'is_published' => true,
        ];
    }

    public function unpublished(): static
    {
        return $this->state(['is_published' => false]);
    }
}
