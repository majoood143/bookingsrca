<?php

namespace App\Livewire\Shop;

use App\Models\BookingSetting;
use App\Models\PosBrand;
use App\Models\PosProduct;
use App\Models\PosProductCategory;
use App\Support\ShopCart;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ShopCatalog extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public ?int $categoryId = null;

    #[Url]
    public ?int $brandId = null;

    #[Url]
    public string $sort = 'newest';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategoryId(): void
    {
        $this->resetPage();
    }

    public function updatingBrandId(): void
    {
        $this->resetPage();
    }

    public function addToCart(int $productId): void
    {
        $product = PosProduct::query()->publishedOnline()->find($productId);

        if (!$product || !$product->isAvailable()) {
            return;
        }

        ShopCart::add($productId, 1);
        $this->dispatch('cart-updated');
    }

    public function render()
    {
        $query = PosProduct::query()->publishedOnline()->with(['category', 'brand']);

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('name_ar', 'like', "%{$this->search}%")
                    ->orWhere('sku', 'like', "%{$this->search}%");
            });
        }

        if ($this->categoryId) {
            $query->where('category_id', $this->categoryId);
        }

        if ($this->brandId) {
            $query->where('brand_id', $this->brandId);
        }

        match ($this->sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'name' => $query->orderBy('name'),
            default => $query->latest(),
        };

        $siteName = BookingSetting::get('site_name_' . app()->getLocale(), config('app.name', 'Bookings'));

        return view('livewire.shop.catalog', [
            'products' => $query->paginate(12),
            'categories' => PosProductCategory::where('is_active', true)->orderBy('order')->get(),
            'brands' => PosBrand::where('is_active', true)->orderBy('order')->get(),
            'cartCount' => ShopCart::count(),
        ])->title(__('shop.catalog.meta_title') . ' | ' . $siteName);
    }
}
