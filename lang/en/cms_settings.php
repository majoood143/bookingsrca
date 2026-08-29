<?php

return [
    'title' => 'CMS Settings',
    'save' => 'Save',
    'tabs' => [
        'general' => 'General',
        'seo' => 'SEO',
    ],
    'sections' => [
        'defaults' => 'Content defaults',
        'defaults_desc' => 'Used as a fallback whenever a page or post does not set its own meta description or image.',
        'seo' => 'SEO & feeds',
    ],
    'fields' => [
        'default_meta_description' => 'Default meta description',
        'default_meta_description_helper' => 'Used when a page or post has no meta description of its own.',
        'default_og_image' => 'Default Open Graph image',
        'default_og_image_helper' => 'Used when a page or post has no Open Graph image of its own.',
        'posts_per_page' => 'Posts per page',
        'sitemap_enabled' => 'Enable sitemap.xml',
        'sitemap_enabled_helper' => 'Publish an auto-generated sitemap covering published pages and posts.',
        'rss_enabled' => 'Enable RSS feed',
        'rss_enabled_helper' => 'Publish /blog/feed with the latest published posts.',
        'robots_block_all' => 'Block all crawlers (staging mode)',
        'robots_block_all_helper' => 'Turn this on for staging environments to keep search engines out via robots.txt.',
    ],
    'notifications' => [
        'updated' => 'Settings updated',
    ],
];
