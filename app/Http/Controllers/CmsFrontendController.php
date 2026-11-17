<?php

namespace App\Http\Controllers;

use App\Models\ArDestination;
use App\Models\BookingSetting;
use App\Models\CmsPage;
use App\Models\CmsPost;
use App\Models\CmsSetting;
use App\Models\Event;
use App\Models\PosProduct;
use Illuminate\Http\Response;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class CmsFrontendController extends Controller
{
    public function sitemap()
    {
        abort_unless((bool) CmsSetting::get('sitemap_enabled', true), 404);

        // '/' always renders the events home page, never a CMS page.
        $sitemap = Sitemap::create()->add(Url::create('/'));

        // Only 'published' — private events stay out of search.
        Event::published()->upcoming()->each(function (Event $event) use ($sitemap) {
            $sitemap->add(
                Url::create(route('event.booking', $event->slug, false))
                    ->setLastModificationDate($event->updated_at)
            );
        });

        $sitemap->add(Url::create(route('shop.index', [], false)));

        PosProduct::publishedOnline()->each(function (PosProduct $product) use ($sitemap) {
            $sitemap->add(
                Url::create(route('shop.product.show', $product->slug, false))
                    ->setLastModificationDate($product->updated_at)
            );
        });

        $sitemap->add(Url::create(route('ar.index', [], false)));

        ArDestination::published()->with(['items' => fn ($q) => $q->published()])->each(function (ArDestination $destination) use ($sitemap) {
            $sitemap->add(Url::create(route('ar.destination', $destination->slug, false))->setLastModificationDate($destination->updated_at));

            foreach ($destination->items as $item) {
                $sitemap->add(Url::create(route('ar.item', [$destination->slug, $item->slug], false))->setLastModificationDate($item->updated_at));
            }
        });

        if ((bool) BookingSetting::get('module_cms_enabled', true)) {
            $sitemap->add(Url::create('/blog'));

            CmsPage::published()->where('is_homepage', false)->each(function (CmsPage $page) use ($sitemap) {
                $sitemap->add(
                    Url::create('/'.$page->slug)
                        ->setLastModificationDate($page->updated_at)
                );
            });

            CmsPost::published()->each(function (CmsPost $post) use ($sitemap) {
                $sitemap->add(
                    Url::create('/blog/'.$post->slug)
                        ->setLastModificationDate($post->updated_at)
                );
            });
        }

        return $sitemap;
    }

    public function robots(): Response
    {
        $blockAll = (bool) CmsSetting::get('robots_block_all', false);

        $lines = $blockAll
            ? ['User-agent: *', 'Disallow: /']
            : [
                'User-agent: *',
                // Transactional, device and admin URLs — no search value, some carry references.
                'Disallow: /admin',
                'Disallow: /install',
                'Disallow: /kiosk/',
                'Disallow: /booking/',
                'Disallow: /shop/checkout',
                'Disallow: /shop/order/',
                'Disallow: /shop/payment/',
                'Disallow: /print-agent/',
                'Disallow: /lang/',
                'Disallow: /livewire/',
                'Disallow: /events/*/signage',
                'Disallow: /play/*/api/',
                'Disallow: /explore/*/scan/',
                'Sitemap: '.url('/sitemap.xml'),
            ];

        return response(implode("\n", $lines)."\n", 200)
            ->header('Content-Type', 'text/plain');
    }

    public function rssFeed(): Response
    {
        abort_if(! BookingSetting::get('module_cms_enabled', true), 404);

        $posts = CmsPost::published()->latest('published_at')->limit(20)->get();

        $locale = app()->getLocale();
        $siteName = BookingSetting::get('site_name_'.$locale, config('app.name', 'Bookings'));

        $xml = view('cms.rss', compact('posts', 'siteName', 'locale'))->render();

        return response($xml, 200)->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }
}
