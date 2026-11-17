<?php

namespace Database\Seeders;

use App\Models\ArDestination;
use Illuminate\Database\Seeder;

/**
 * Demo AR destination (Piazza dei Miracoli, Pisa) for trying the explore pages and the scanner.
 * Safe to re-run: it rebuilds the demo destination from database/seeders/assets/ar-pisa.
 * After seeding, open each zone in the admin and click "Compile AR targets".
 */
class ArPisaDemoSeeder extends Seeder
{
    public function run(): void
    {
        $dir = database_path('seeders/assets/ar-pisa');
        $content = json_decode(file_get_contents("{$dir}/content.json"), true, flags: JSON_THROW_ON_ERROR);
        $data = $content['destination'];

        // Delete through Eloquent so Media Library removes the old files too.
        ArDestination::where('slug', $data['slug'])->with('zones.items')->get()->each(function (ArDestination $old) {
            $old->zones->each(fn ($zone) => $zone->items->each->delete());
            $old->zones->each->delete();
            $old->delete();
        });

        $destination = ArDestination::create([
            'slug' => $data['slug'],
            'title' => $data['title'],
            'summary' => $data['summary'],
            'description' => $data['description'],
            'is_published' => true,
        ]);
        $destination->addMedia("{$dir}/{$data['cover']}")->preservingOriginal()->toMediaCollection('cover');

        foreach ($content['zones'] as $zoneOrder => $zoneData) {
            $zone = $destination->zones()->create(['title' => $zoneData['title'], 'sort_order' => $zoneOrder]);

            foreach ($zoneData['items'] as $itemOrder => $itemData) {
                $item = $zone->items()->create([
                    'slug' => $itemData['slug'],
                    'title' => $itemData['title'],
                    'summary' => $itemData['summary'],
                    'body' => $itemData['body'],
                    'sort_order' => $itemOrder,
                    'is_published' => true,
                ]);

                $item->addMedia("{$dir}/{$itemData['image']}")->preservingOriginal()->toMediaCollection('target');

                foreach (array_keys($itemData['title']) as $locale) {
                    $audio = "{$dir}/{$itemData['slug']}-{$locale}.m4a";
                    if (is_file($audio)) {
                        $item->addMedia($audio)->preservingOriginal()
                            ->withCustomProperties(['locale' => $locale])
                            ->toMediaCollection('audio');
                    }
                }
            }
        }

        $this->command?->info("Seeded {$destination->title}. Now compile each zone in Admin → AR Explore → Destinations → Zones.");
    }
}
