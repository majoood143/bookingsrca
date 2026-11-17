<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\PosProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    private function event(string $slug, string $status): Event
    {
        return Event::create([
            'title' => ['en' => 'Test Event', 'ar' => 'فعالية تجريبية'],
            'description' => ['en' => 'desc', 'ar' => 'وصف'],
            'slug' => $slug,
            'location' => ['en' => 'Test Venue', 'ar' => 'مكان'],
            'organizer' => 'Test Org',
            'start_date' => now()->addDay()->format('Y-m-d'),
            'end_date' => now()->addMonth()->format('Y-m-d'),
            'status' => $status,
            'max_attendees' => 100,
        ]);
    }

    private function product(): PosProduct
    {
        return PosProduct::create([
            'name' => 'Farm Honey Jar',
            'slug' => 'farm-honey-jar',
            'sku' => 'HJ-1',
            'price' => 3,
            'tax_rate' => 5,
            'stock_quantity' => 10,
            'is_active' => true,
            'is_published_online' => true,
        ]);
    }

    public function test_sitemap_lists_public_events_and_products_but_not_private_events(): void
    {
        $this->event('open-day', 'published');
        $this->event('vip-night', 'private');
        $this->product();

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee('/events/open-day', false)
            ->assertSee('/shop/farm-honey-jar', false)
            ->assertDontSee('vip-night', false);
    }

    public function test_robots_blocks_transactional_paths_and_points_to_sitemap(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Disallow: /booking/')
            ->assertSee('Disallow: /kiosk/')
            ->assertSee('Sitemap: '.url('/sitemap.xml'));
    }

    public function test_product_page_has_canonical_and_product_schema(): void
    {
        $product = $this->product();

        $this->get(route('shop.product.show', $product->slug))
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.route('shop.product.show', $product->slug).'">', false)
            ->assertSee('"@type":"Product"', false);
    }
}
