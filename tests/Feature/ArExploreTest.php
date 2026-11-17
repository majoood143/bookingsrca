<?php

namespace Tests\Feature;

use App\Models\ArDestination;
use App\Models\ArItem;
use App\Models\ArZone;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ArExploreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->withoutVite();
    }

    private function itemWithTarget(ArZone $zone, array $attributes = []): ArItem
    {
        $item = ArItem::factory()->for($zone, 'zone')->create($attributes);
        $item->addMedia(UploadedFile::fake()->image('target.jpg'))->toMediaCollection('target');

        return $item;
    }

    private function compile(ArZone $zone): void
    {
        $zone->storeCompiledTargets(
            UploadedFile::fake()->create('targets.mind', 10, 'application/octet-stream'),
            $zone->targetItems()->pluck('id')->all(),
        );
    }

    private function admin(bool $canUpdate = true): User
    {
        $role = Role::findOrCreate('ar_editor', 'web');
        if ($canUpdate) {
            $role->givePermissionTo(Permission::findOrCreate('Update:ArDestination', 'web'));
        }

        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($role);

        return $user;
    }

    public function test_public_pages_show_only_published_content(): void
    {
        $zone = ArZone::factory()->create();
        $destination = $zone->destination;
        $item = ArItem::factory()->for($zone, 'zone')->create(['title' => ['en' => 'Old Fort Gate', 'ar' => 'Old Fort Gate']]);
        $hiddenItem = ArItem::factory()->for($zone, 'zone')->unpublished()->create(['title' => ['en' => 'Secret Room', 'ar' => 'Secret Room']]);
        $hiddenDestination = ArDestination::factory()->unpublished()->create(['title' => ['en' => 'Closed Museum', 'ar' => 'Closed Museum']]);

        $this->get(route('ar.index'))->assertOk()
            ->assertSee($destination->title)
            ->assertDontSee('Closed Museum');

        $this->get(route('ar.destination', $destination->slug))->assertOk()
            ->assertSee('Old Fort Gate')
            ->assertDontSee('Secret Room')
            ->assertSee('<link rel="canonical" href="'.route('ar.destination', $destination->slug).'">', false)
            ->assertSee('"@type":"TouristDestination"', false);

        $this->get(route('ar.item', [$destination->slug, $item->slug]))->assertOk()
            ->assertSee('"@type":"TouristAttraction"', false);

        $this->get(route('ar.item', [$destination->slug, $hiddenItem->slug]))->assertNotFound();
        $this->get(route('ar.destination', $hiddenDestination->slug))->assertNotFound();
    }

    public function test_item_url_is_scoped_to_its_destination(): void
    {
        $item = ArItem::factory()->create();
        $other = ArDestination::factory()->create();

        $this->get(route('ar.item', [$other->slug, $item->slug]))->assertNotFound();
    }

    public function test_scan_page_needs_a_compiled_zone_and_skips_items_unpublished_since(): void
    {
        $zone = ArZone::factory()->create();
        $slug = $zone->destination->slug;
        $kept = $this->itemWithTarget($zone, ['title' => ['en' => 'Kept Statue', 'ar' => 'Kept Statue']]);
        $dropped = $this->itemWithTarget($zone, ['title' => ['en' => 'Dropped Vase', 'ar' => 'Dropped Vase']]);

        $this->get(route('ar.scan', [$slug, $zone]))->assertNotFound();

        $this->compile($zone);
        $dropped->update(['is_published' => false]);

        $cards = $zone->fresh()->scannerCards();
        $this->assertSame('Kept Statue', $cards[0]['title']);
        $this->assertNull($cards[1]);

        $this->get(route('ar.scan', [$slug, $zone]))->assertOk()
            ->assertSee('noindex', false)
            ->assertSee('Kept Statue')
            ->assertDontSee('Dropped Vase');

        $otherZone = ArZone::factory()->create();
        $this->get(route('ar.scan', [$slug, $otherZone]))->assertNotFound();
    }

    public function test_needs_compile_tracks_new_items_and_new_target_images(): void
    {
        $zone = ArZone::factory()->create();
        $item = $this->itemWithTarget($zone);
        $this->assertTrue($zone->needsCompile());

        $this->compile($zone);
        $this->assertFalse($zone->fresh()->needsCompile());

        $this->travel(1)->minutes();
        $item->addMedia(UploadedFile::fake()->image('new.jpg'))->toMediaCollection('target');
        $this->assertTrue($zone->fresh()->needsCompile());

        $this->compile($zone);
        $this->itemWithTarget($zone);
        $this->assertTrue($zone->fresh()->needsCompile());
    }

    public function test_narration_is_per_language_without_fallback(): void
    {
        $item = ArItem::factory()->create();
        $item->addMedia(UploadedFile::fake()->create('ar.mp3', 10, 'audio/mpeg'))
            ->withCustomProperties(['locale' => 'ar'])->toMediaCollection('audio');

        $this->assertStringContainsString('ar.mp3', $item->audioUrl('ar'));
        $this->assertNull($item->audioUrl('en'));
    }

    public function test_admin_upload_of_compiled_targets(): void
    {
        $zone = ArZone::factory()->create();
        $item = $this->itemWithTarget($zone);
        $url = route('ar.zone.targets', $zone);
        $payload = fn (array $ids) => ['file' => UploadedFile::fake()->create('targets.mind', 10, 'application/octet-stream'), 'item_ids' => $ids];

        $this->postJson($url, $payload([$item->id]))->assertUnauthorized();
        $this->actingAs($this->admin(canUpdate: false))->post($url, $payload([$item->id]))->assertForbidden();

        // The items changed while the browser was compiling: refuse, or indexes would point at the wrong items.
        $this->actingAs($this->admin())->postJson($url, $payload([$item->id, 999]))->assertStatus(422);
        $this->assertFalse($zone->fresh()->isScannable());

        $this->actingAs($this->admin())->postJson($url, $payload([$item->id]))->assertOk();
        $this->assertSame([$item->id], $zone->fresh()->target_order);
        $this->assertTrue($zone->fresh()->isScannable());
    }

    public function test_sitemap_and_robots(): void
    {
        $item = ArItem::factory()->create();
        $hidden = ArItem::factory()->unpublished()->create();

        $this->get('/sitemap.xml')->assertOk()
            ->assertSee('/explore/'.$item->zone->destination->slug.'/'.$item->slug, false)
            ->assertDontSee($hidden->slug, false);

        $this->get('/robots.txt')->assertSee('Disallow: /explore/*/scan/');
    }
}
