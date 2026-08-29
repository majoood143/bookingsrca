<?php

namespace App\Livewire\Cms;

use App\Models\BookingSetting;
use App\Models\CmsPage;
use App\Models\CmsRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class CmsPageShow extends Component
{
    public CmsPage $page;

    public function mount(string $slug): ?RedirectResponse
    {
        abort_if(!BookingSetting::get('module_cms_enabled', true), 404);

        $page = CmsPage::published()->where('slug', $slug)->first();

        if ($page) {
            $this->page = $page;

            return null;
        }

        $redirect = CmsRedirect::findForPath($slug);

        if ($redirect) {
            $redirect->recordHit();

            return redirect($redirect->to_path, $redirect->status_code);
        }

        abort(404);
    }

    public function render()
    {
        $title = $this->page->getTranslation('meta_title', app()->getLocale())
            ?: $this->page->getTranslation('title', app()->getLocale());

        $renderedContent = Cache::remember(
            "cms-page-content-{$this->page->id}-{$this->page->updated_at->timestamp}-".app()->getLocale(),
            3600,
            fn () => view('components.cms.block-renderer', ['blocks' => $this->page->content ?? []])->render()
        );

        return view('livewire.cms.page-show', ['renderedContent' => $renderedContent])->title($title);
    }
}
