<?php

namespace App\Console\Commands;

use App\Models\CmsPage;
use App\Models\CmsPost;
use Illuminate\Console\Command;

class PublishScheduledCmsContent extends Command
{
    protected $signature = 'cms:publish-scheduled';

    protected $description = 'Flip scheduled CMS pages and posts to published once their publish date has passed.';

    public function handle(): int
    {
        $published = 0;

        foreach ([CmsPage::class, CmsPost::class] as $model) {
            $published += $model::where('status', 'scheduled')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->update(['status' => 'published']);
        }

        $this->info("Published {$published} scheduled CMS item(s).");

        return self::SUCCESS;
    }
}
