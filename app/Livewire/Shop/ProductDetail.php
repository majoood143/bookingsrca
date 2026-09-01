<?php

namespace App\Livewire\Shop;

use App\Models\BookingSetting;
use App\Models\PosOrder;
use App\Models\PosProduct;
use App\Models\PosProductReview;
use App\Support\ShopCart;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ProductDetail extends Component
{
    public PosProduct $product;

    public int $quantity = 1;

    public string $activeTab = 'overview';

    // Verified-purchase review form: the customer proves they bought this
    // product by supplying the order number + email it was placed under,
    // since there are no shopper accounts to check purchase history against.
    #[Validate('required|string')]
    public string $reviewOrderNumber = '';

    #[Validate('required|email')]
    public string $reviewEmail = '';

    #[Validate('required|integer|min:1|max:5')]
    public int $reviewRating = 5;

    #[Validate('nullable|string|max:255')]
    public string $reviewTitle = '';

    #[Validate('required|string|max:2000')]
    public string $reviewBody = '';

    public bool $reviewSubmitted = false;

    public function mount(): void
    {
        abort_unless($this->product->is_active && $this->product->is_published_online, 404);

        $this->product->increment('views_count');
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = in_array($tab, ['overview', 'description', 'reviews'], true) ? $tab : 'overview';
    }

    public function addToCart(): void
    {
        if (!$this->product->isAvailable($this->quantity)) {
            $this->addError('quantity', __('shop.product.out_of_stock'));

            return;
        }

        ShopCart::add($this->product->id, max(1, $this->quantity));
        $this->dispatch('cart-updated');
        $this->dispatch('added-to-cart');
    }

    public function submitReview(): void
    {
        $this->validate();

        $order = PosOrder::where('order_number', $this->reviewOrderNumber)
            ->where('contact_email', $this->reviewEmail)
            ->first();

        $item = $order ? PosProductReview::eligibleOrderItem($order, $this->product->id) : null;

        if (!$item) {
            $this->addError('reviewOrderNumber', __('shop.product.review_not_eligible'));

            return;
        }

        $existing = PosProductReview::where('product_id', $this->product->id)->where('order_id', $order->id)->exists();

        if ($existing) {
            $this->addError('reviewOrderNumber', __('shop.product.review_already_submitted'));

            return;
        }

        DB::transaction(function () use ($order) {
            PosProductReview::create([
                'product_id' => $this->product->id,
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'rating' => $this->reviewRating,
                'title' => $this->reviewTitle ?: null,
                'body' => $this->reviewBody,
                'is_approved' => false,
            ]);
        });

        $this->reset(['reviewOrderNumber', 'reviewEmail', 'reviewTitle', 'reviewBody']);
        $this->reviewRating = 5;
        $this->reviewSubmitted = true;
    }

    public function render()
    {
        $siteName = BookingSetting::get('site_name_' . app()->getLocale(), config('app.name', 'Bookings'));

        $related = PosProduct::query()
            ->publishedOnline()
            ->where('id', '!=', $this->product->id)
            ->when($this->product->category_id, fn ($q) => $q->where('category_id', $this->product->category_id))
            ->limit(4)
            ->get();

        return view('livewire.shop.product-detail', [
            'reviews' => $this->product->approvedReviews()->with('customer')->latest()->get(),
            'related' => $related,
            'cartCount' => ShopCart::count(),
        ])->title($this->product->display_name . ' | ' . $siteName);
    }
}
