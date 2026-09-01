<?php

namespace App\Support;

use App\Models\PosProduct;
use Illuminate\Support\Collection;

/**
 * Session-backed guest cart for the online POS shop. No accounts exist for
 * shoppers, so the cart lives entirely in the session rather than a table —
 * it only needs to survive one browsing session up to checkout.
 */
class ShopCart
{
    private const SESSION_KEY = 'shop_cart';

    // Each entry: ['product' => PosProduct, 'quantity' => int]. Stale rows
    // (deleted/unpublished/out-of-stock products) are silently dropped, and
    // quantities are clamped to current stock, so the cart never offers more
    // than what's actually available.
    public static function items(): Collection
    {
        $raw = self::raw();

        if (empty($raw)) {
            return collect();
        }

        $products = PosProduct::query()->publishedOnline()->whereIn('id', array_keys($raw))->get()->keyBy('id');

        return collect($raw)
            ->map(function (int $quantity, int $productId) use ($products) {
                $product = $products->get($productId);

                if (!$product || $product->stock_quantity <= 0) {
                    return null;
                }

                return ['product' => $product, 'quantity' => min($quantity, $product->stock_quantity)];
            })
            ->filter()
            ->values();
    }

    public static function add(int $productId, int $quantity = 1): void
    {
        $cart = self::raw();
        $cart[$productId] = ((int) ($cart[$productId] ?? 0)) + $quantity;
        self::save(self::clamp($productId, $cart[$productId]), $cart, $productId);
    }

    public static function updateQuantity(int $productId, int $quantity): void
    {
        $cart = self::raw();

        if ($quantity <= 0) {
            unset($cart[$productId]);
            session([self::SESSION_KEY => $cart]);

            return;
        }

        self::save(self::clamp($productId, $quantity), $cart, $productId);
    }

    public static function remove(int $productId): void
    {
        $cart = self::raw();
        unset($cart[$productId]);
        session([self::SESSION_KEY => $cart]);
    }

    public static function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public static function count(): int
    {
        return (int) array_sum(self::raw());
    }

    public static function subtotal(): float
    {
        return (float) self::items()->sum(fn (array $item) => $item['product']->price * $item['quantity']);
    }

    public static function taxTotal(): float
    {
        return (float) self::items()->sum(fn (array $item) => round(
            $item['product']->price * $item['quantity'] * ($item['product']->tax_rate / 100),
            3
        ));
    }

    public static function isEmpty(): bool
    {
        return self::items()->isEmpty();
    }

    private static function raw(): array
    {
        return session(self::SESSION_KEY, []);
    }

    private static function clamp(int $productId, int $quantity): int
    {
        $product = PosProduct::find($productId);

        return $product ? max(0, min($quantity, $product->stock_quantity)) : 0;
    }

    private static function save(int $quantity, array $cart, int $productId): void
    {
        if ($quantity <= 0) {
            unset($cart[$productId]);
        } else {
            $cart[$productId] = $quantity;
        }

        session([self::SESSION_KEY => $cart]);
    }
}
