<?php

namespace App\Http\Controllers;

use App\Models\BookingSetting;
use App\Models\CmsPage;
use App\Models\CmsPost;
use App\Models\CmsSetting;
use Illuminate\Http\Response;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class CmsFrontendController extends Controller
{
    public function sitemap()
    {
        if (!(bool) BookingSetting::get('module_cms_enabled', true) || !(bool) CmsSetting::get('sitemap_enabled', true)) {
            abort(404);
        }

        $sitemap = Sitemap::create();

        CmsPage::published()->each(function (CmsPage $page) use ($sitemap) {
            $sitemap->add(
                Url::create($page->is_homepage ? '/' : '/'.$page->slug)
                    ->setLastModificationDate($page->updated_at)
            );
        });

        CmsPost::published()->each(function (CmsPost $post) use ($sitemap) {
            $sitemap->add(
                Url::create('/blog/'.$post->slug)
                    ->setLastModificationDate($post->updated_at)
            );
        });

        return $sitemap;
    }

    public function robots(): Response
    {
        $blockAll = (bool) CmsSetting::get('robots_block_all', false);

        $lines = $blockAll
            ? ["User-agent: *", "Disallow: /"]
            : ["User-agent: *", "Disallow:", "Sitemap: ".url('/sitemap.xml')];

        return response(implode("\n", $lines)."\n", 200)
            ->header('Content-Type', 'text/plain');
    }

    public function rssFeed(): Response
    {
        abort_if(!BookingSetting::get('module_cms_enabled', true), 404);

        $posts = CmsPost::published()->latest('published_at')->limit(20)->get();

        $locale = app()->getLocale();
        $siteName = \App\Models\BookingSetting::get('site_name_'.$locale, config('app.name', 'Bookings'));

        $xml = view('cms.rss', compact('posts', 'siteName', 'locale'))->render();

        return response($xml, 200)->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }
}
