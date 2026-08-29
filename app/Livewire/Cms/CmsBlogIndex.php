<?php

namespace App\Livewire\Cms;

use App\Models\BookingSetting;
use App\Models\CmsCategory;
use App\Models\CmsPost;
use App\Models\CmsSetting;
use Livewire\Component;
use Livewire\WithPagination;

class CmsBlogIndex extends Component
{
    use WithPagination;

    public ?string $categorySlug = null;

    public function mount(?string $categorySlug = null): void
    {
        abort_if(!BookingSetting::get('module_cms_enabled', true), 404);

        $this->categorySlug = $categorySlug;
    }

    public function render()
    {
        $category = $this->categorySlug
            ? CmsCategory::where('slug', $this->categorySlug)->firstOrFail()
            : null;

        $posts = CmsPost::published()
            ->when($category, fn ($query) => $query->where('category_id', $category->id))
            ->latest('published_at')
            ->paginate((int) CmsSetting::get('posts_per_page', 10));

        return view('livewire.cms.blog-index', [
            'posts' => $posts,
            'category' => $category,
        ]);
    }
}
