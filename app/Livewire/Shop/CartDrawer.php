<?php

namespace App\Livewire\Shop;

use App\Support\ShopCart;
use Livewire\Attributes\On;
use Livewire\Component;

class CartDrawer extends Component
{
    public bool $open = false;

    #[On('cart-updated')]
    public function refresh(): void
    {
        // No-op body: the #[On] attribute alone forces a re-render, which is
        // all this component needs — its state lives entirely in the session
        // via ShopCart, not on the component itself.
    }

    public function updateQuantity(int $productId, int $quantity): void
    {
        ShopCart::updateQuantity($productId, $quantity);
    }

    public function removeItem(int $productId): void
    {
        ShopCart::remove($productId);
    }

    public function render()
    {
        return view('livewire.shop.cart-drawer', [
            'items' => ShopCart::items(),
            'subtotal' => ShopCart::subtotal(),
        ]);
    }
}
