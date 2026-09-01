<?php

namespace App\Livewire\Shop;

use App\Models\BookingSetting;
use App\Models\PosCustomer;
use App\Models\PosOrder;
use App\Services\CCAvenueService;
use App\Services\NboService;
use App\Services\ThawaniService;
use App\Support\ShopCart;
use Exception;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Checkout extends Component
{
    public string $contactName = '';
    public string $contactEmail = '';
    public string $contactMobile = '';
    public string $addressLine1 = '';
    public string $addressLine2 = '';
    public string $city = '';
    public string $state = '';
    public string $country = '';
    public string $zipCode = '';
    public string $customerNote = '';

    public array $enabledGateways = [];
    public string $selectedPaymentMethod = '';

    public function mount(): void
    {
        $this->enabledGateways = array_values(array_filter(
            BookingSetting::enabledGateways(),
            fn ($gateway) => in_array($gateway, ['thawani', 'nbo', 'ccavenue'], true)
        ));
        $this->selectedPaymentMethod = $this->enabledGateways[0] ?? '';
    }

    public function selectGateway(string $gateway): void
    {
        if (in_array($gateway, $this->enabledGateways, true)) {
            $this->selectedPaymentMethod = $gateway;
        }
    }

    protected function rules(): array
    {
        return [
            'contactName' => 'required|string|max:255',
            'contactEmail' => 'required|email|max:255',
            'contactMobile' => 'required|string|max:50',
            'addressLine1' => 'nullable|string|max:255',
            'addressLine2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'zipCode' => 'nullable|string|max:50',
            'customerNote' => 'nullable|string|max:1000',
        ];
    }

    public function placeOrder()
    {
        $this->validate();

        if (ShopCart::isEmpty()) {
            session()->flash('error', __('shop.checkout.cart_empty'));

            return;
        }

        if (!$this->selectedPaymentMethod) {
            session()->flash('error', __('shop.checkout.no_gateway'));

            return;
        }

        $items = ShopCart::items();
        $subtotal = ShopCart::subtotal();
        $taxTotal = ShopCart::taxTotal();
        $grandTotal = round($subtotal + $taxTotal, 3);

        $order = DB::transaction(function () use ($items, $subtotal, $taxTotal, $grandTotal) {
            $customer = PosCustomer::findOrCreateForGuestCheckout([
                'name' => $this->contactName,
                'email' => $this->contactEmail,
                'mobile' => $this->contactMobile,
                'address_line_1' => $this->addressLine1 ?: null,
                'address_line_2' => $this->addressLine2 ?: null,
                'city' => $this->city ?: null,
                'state' => $this->state ?: null,
                'country' => $this->country ?: null,
                'zip_code' => $this->zipCode ?: null,
            ]);

            $order = PosOrder::create([
                'order_number' => PosOrder::nextOrderNumber(),
                'customer_id' => $customer->id,
                'contact_name' => $this->contactName,
                'contact_email' => $this->contactEmail,
                'contact_mobile' => $this->contactMobile,
                'address_line_1' => $this->addressLine1 ?: null,
                'address_line_2' => $this->addressLine2 ?: null,
                'city' => $this->city ?: null,
                'state' => $this->state ?: null,
                'country' => $this->country ?: null,
                'zip_code' => $this->zipCode ?: null,
                'subtotal' => $subtotal,
                'tax_total' => $taxTotal,
                'grand_total' => $grandTotal,
                'customer_note' => $this->customerNote ?: null,
            ]);

            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->display_name,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['product']->price,
                    'line_total' => round($item['product']->price * $item['quantity'], 3),
                ]);
            }

            return $order;
        });

        return match ($this->selectedPaymentMethod) {
            'thawani' => $this->redirectToThawani($order),
            'nbo' => $this->redirectToNbo($order),
            'ccavenue' => $this->redirectToCcavenue($order),
            default => null,
        };
    }

    protected function redirectToThawani(PosOrder $order)
    {
        try {
            $thawani = app(ThawaniService::class);

            if (!$thawani->isConfigured()) {
                throw new Exception(__('shop.checkout.gateway_not_configured'));
            }

            $products = $order->items->map(fn ($item) => [
                'name' => $item->product_name,
                'quantity' => $item->quantity,
                'unit_amount' => $thawani->toBasisa((float) $item->unit_price),
            ])->all();

            $taxBasisa = $thawani->toBasisa((float) $order->tax_total);
            if ($taxBasisa > 0) {
                $products[] = ['name' => __('shop.checkout.tax_line'), 'quantity' => 1, 'unit_amount' => $taxBasisa];
            }

            $response = $thawani->createSession([
                'client_reference_id' => $order->order_number,
                'products' => $products,
                'success_url' => route('shop.payment.callback') . '?reference=' . $order->order_number,
                'cancel_url' => route('shop.checkout'),
                'metadata' => ['pos_order_id' => $order->id],
            ], $order);

            $sessionId = $response['data']['session_id'] ?? null;

            if (!$sessionId) {
                throw new Exception(__('shop.checkout.session_failed'));
            }

            $order->update(['payment_method' => 'thawani', 'payment_reference' => $sessionId]);

            return redirect()->away($thawani->getCheckoutUrl($sessionId));
        } catch (Exception $e) {
            $order->update(['status' => 'failed']);
            session()->flash('error', $e->getMessage());
        }
    }

    protected function redirectToNbo(PosOrder $order)
    {
        try {
            $nbo = app(NboService::class);

            if (!$nbo->isConfigured()) {
                throw new Exception(__('shop.checkout.gateway_not_configured'));
            }

            return redirect()->away($nbo->initiateOrderPayment($order));
        } catch (Exception $e) {
            $order->update(['status' => 'failed']);
            session()->flash('error', $e->getMessage());
        }
    }

    protected function redirectToCcavenue(PosOrder $order)
    {
        try {
            $ccavenue = app(CCAvenueService::class);

            if (!$ccavenue->isConfigured()) {
                throw new Exception(__('shop.checkout.gateway_not_configured'));
            }

            return redirect()->route('shop.payment.redirect.ccavenue', $order->order_number);
        } catch (Exception $e) {
            $order->update(['status' => 'failed']);
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $siteName = BookingSetting::get('site_name_' . app()->getLocale(), config('app.name', 'Bookings'));

        return view('livewire.shop.checkout', [
            'items' => ShopCart::items(),
            'subtotal' => ShopCart::subtotal(),
            'taxTotal' => ShopCart::taxTotal(),
            'grandTotal' => round(ShopCart::subtotal() + ShopCart::taxTotal(), 3),
        ])->title(__('shop.checkout.meta_title') . ' | ' . $siteName);
    }
}
