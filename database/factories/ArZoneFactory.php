<?php

namespace Database\Factories;

use App\Models\ArDestination;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArZoneFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ar_destination_id' => ArDestination::factory(),
            'title' => ['en' => 'Hall '.fake()->randomLetter(), 'ar' => 'قاعة'],
        ];
    }
}
