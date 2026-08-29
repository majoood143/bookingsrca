<?php

namespace App\Livewire\Cms;

use App\Models\BookingSetting;
use App\Models\CmsPost;
use App\Models\CmsRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class CmsPostShow extends Component
{
    public CmsPost $post;

    public function mount(string $slug): ?RedirectResponse
    {
        abort_if(!BookingSetting::get('module_cms_enabled', true), 404);

        $post = CmsPost::published()->where('slug', $slug)->first();

        if ($post) {
            $this->post = $post;

            return null;
        }

        $redirect = CmsRedirect::findForPath('blog/'.$slug);

        if ($redirect) {
            $redirect->recordHit();

            return redirect($redirect->to_path, $redirect->status_code);
        }

        abort(404);
    }

    public function render()
    {
        $title = $this->post->getTranslation('meta_title', app()->getLocale())
            ?: $this->post->getTranslation('title', app()->getLocale());

        $renderedContent = Cache::remember(
            "cms-post-content-{$this->post->id}-{$this->post->updated_at->timestamp}-".app()->getLocale(),
            3600,
            fn () => view('components.cms.block-renderer', ['blocks' => $this->post->content ?? []])->render()
        );

        return view('livewire.cms.post-show', ['renderedContent' => $renderedContent])->title($title);
    }
}
