<?php

namespace Database\Seeders;

use App\Models\CmsCategory;
use App\Models\CmsMenu;
use App\Models\CmsMenuItem;
use App\Models\CmsPage;
use App\Models\CmsPost;
use Illuminate\Database\Seeder;

class CmsDemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $home = CmsPage::create([
            'title' => ['en' => 'Home', 'ar' => 'الرئيسية'],
            'slug' => 'home',
            'excerpt' => ['en' => 'Welcome to our site.', 'ar' => 'مرحبًا بكم في موقعنا.'],
            'status' => 'published',
            'published_at' => now(),
            'is_homepage' => true,
            'content' => [
                [
                    'type' => 'hero',
                    'data' => [
                        'heading' => ['en' => 'Welcome', 'ar' => 'مرحبًا'],
                        'subheading' => ['en' => 'Discover our events and services.', 'ar' => 'اكتشف فعالياتنا وخدماتنا.'],
                        'cta_text' => ['en' => 'Browse Events', 'ar' => 'تصفح الفعاليات'],
                        'cta_url' => '/',
                    ],
                ],
            ],
        ]);

        CmsPage::create([
            'title' => ['en' => 'About Us', 'ar' => 'من نحن'],
            'slug' => 'about-us',
            'status' => 'published',
            'published_at' => now(),
            'content' => [
                [
                    'type' => 'rich_text',
                    'data' => [
                        'content' => [
                            'en' => '<p>We are dedicated to providing a great booking experience.</p>',
                            'ar' => '<p>نحن ملتزمون بتقديم تجربة حجز رائعة.</p>',
                        ],
                    ],
                ],
            ],
        ]);

        $category = CmsCategory::create([
            'name' => ['en' => 'Announcements', 'ar' => 'الإعلانات'],
            'slug' => 'announcements',
        ]);

        CmsPost::create([
            'title' => ['en' => 'Hello World', 'ar' => 'مرحبًا بالعالم'],
            'slug' => 'hello-world',
            'excerpt' => ['en' => 'Our first blog post.', 'ar' => 'أول مقالة في مدونتنا.'],
            'status' => 'published',
            'published_at' => now(),
            'category_id' => $category->id,
            'content' => [
                [
                    'type' => 'rich_text',
                    'data' => [
                        'content' => [
                            'en' => '<p>This is our first announcement.</p>',
                            'ar' => '<p>هذا أول إعلان لنا.</p>',
                        ],
                    ],
                ],
            ],
        ]);

        $menu = CmsMenu::create([
            'name' => 'Main Menu',
            'slug' => 'main-menu',
            'location' => 'header',
        ]);

        CmsMenuItem::create([
            'menu_id' => $menu->id,
            'label' => ['en' => 'Home', 'ar' => 'الرئيسية'],
            'linkable_type' => CmsPage::class,
            'linkable_id' => $home->id,
            'sort_order' => 0,
        ]);

        CmsMenuItem::create([
            'menu_id' => $menu->id,
            'label' => ['en' => 'Blog', 'ar' => 'المدونة'],
            'url' => '/blog',
            'sort_order' => 1,
        ]);
    }
}
