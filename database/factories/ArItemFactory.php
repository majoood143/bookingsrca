<?php

namespace Database\Factories;

use App\Models\ArZone;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ArItemFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->words(3, true);

        return [
            'ar_zone_id' => ArZone::factory(),
            'slug' => Str::slug($title).'-'.Str::random(4),
            'title' => ['en' => $title, 'ar' => $title],
            'summary' => ['en' => fake()->sentence(), 'ar' => fake()->sentence()],
            'body' => ['en' => fake()->paragraph(), 'ar' => fake()->paragraph()],
            'is_published' => true,
        ];
    }

    public function unpublished(): static
    {
        return $this->state(['is_published' => false]);
    }
}
