@php
    use App\Models\BookingSetting;
    use Illuminate\Support\Str;

    $locale = app()->getLocale();
    $isRtl = $locale === 'ar';
    $primaryColor = BookingSetting::get('primary_color', '#05602b');
    $secondaryColor = BookingSetting::get('secondary_color', '#0da74c');
    $galleryUrls = $product->gallery_urls;
@endphp
<div dir="{{ $isRtl ? 'rtl' : 'ltr' }}" style="--color-brand: {{ $primaryColor }}; --color-brand-hover: {{ $secondaryColor }};">
    @include('shop.partials.header')

    <main class="max-w-6xl mx-auto px-4 sm:px-6 py-8 sm:py-12">
        {{-- Breadcrumb --}}
        <nav class="text-sm text-gray-400 mb-6 flex items-center gap-2 flex-wrap">
            <a href="{{ route('shop.index') }}" class="hover:text-brand">{{ __('shop.nav.shop') }}</a>
            @if ($product->category)
                <span>/</span>
                <span>{{ $product->category->full_name }}</span>
            @endif
            <span>/</span>
            <span class="text-gray-600 font-medium">{{ $product->display_name }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
            {{-- Gallery --}}
            <div class="pos-product-gallery" x-data="{ active: 0, urls: @js($galleryUrls) }"
                x-init="$nextTick(() => window.gsap && gsap.from($refs.mainImage, { opacity: 0, scale: 0.97, duration: 0.5, ease: 'power2.out' }))">
                <div class="aspect-square rounded-2xl bg-gray-100 overflow-hidden">
                    @if (count($galleryUrls) > 0)
                        <template x-for="(url, i) in urls" :key="i">
                            <img :src="url" x-show="active === i" x-ref="mainImage" class="w-full h-full object-cover">
                        </template>
                    @else
                        <div class="w-full h-full flex items-center justify-center text-6xl">📦</div>
                    @endif
                </div>

                @if (count($galleryUrls) > 1)
                    <div class="mt-3 grid grid-cols-5 gap-2">
                        <template x-for="(url, i) in urls" :key="'thumb-' + i">
                            <button type="button"
                                @click="
                                    if (window.gsap) {
                                        gsap.fromTo($refs.mainImage, { opacity: 0 }, { opacity: 1, duration: 0.35, ease: 'power1.out' });
                                    }
                                    active = i;
                                "
                                class="aspect-square rounded-lg overflow-hidden border-2 transition-colors"
                                :class="active === i ? 'border-brand' : 'border-transparent'">
                                <img :src="url" class="w-full h-full object-cover">
                            </button>
                        </template>
                    </div>
                @endif
            </div>

            {{-- Info --}}
            <div class="pos-product-info">
                @if ($product->brand)
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">{{ $product->brand->display_name }}</span>
                @endif

                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mt-1">{{ $product->display_name }}</h1>

                @if ($product->reviews_count > 0)
                    <div class="flex items-center gap-2 mt-2">
                        <span class="text-amber-500 text-sm">{{ str_repeat('★', round($product->average_rating)) }}{{ str_repeat('☆', 5 - round($product->average_rating)) }}</span>
                        <span class="text-sm text-gray-400">({{ $product->reviews_count }})</span>
                    </div>
                @endif

                <div class="text-3xl font-bold text-gray-900 mt-4">
                    @include('partials.currency-amount', ['amount' => (float) $product->price])
                </div>

                <dl class="mt-6 space-y-2 text-sm">
                    <div class="flex gap-2">
                        <dt class="text-gray-400 w-32 shrink-0">{{ __('shop.product.sku') }}</dt>
                        <dd class="text-gray-700">{{ $product->sku }}</dd>
                    </div>
                    @if ($product->category)
                        <div class="flex gap-2">
                            <dt class="text-gray-400 w-32 shrink-0">{{ __('shop.product.category') }}</dt>
                            <dd class="text-gray-700">{{ $product->category->full_name }}</dd>
                        </div>
                    @endif
                    @if ($product->brand)
                        <div class="flex gap-2">
                            <dt class="text-gray-400 w-32 shrink-0">{{ __('shop.product.brand') }}</dt>
                            <dd class="text-gray-700">{{ $product->brand->display_name }}</dd>
                        </div>
                    @endif
                    <div class="flex gap-2">
                        <dt class="text-gray-400 w-32 shrink-0">{{ __('shop.product.availability') }}</dt>
                        <dd>
                            @if (!$product->isAvailable())
                                <span class="text-danger-600 font-medium">{{ __('shop.product.out_of_stock') }}</span>
                            @elseif ($product->isLowStock())
                                <span class="text-amber-600 font-medium">{{ __('shop.product.low_stock', ['count' => $product->stock_quantity]) }}</span>
                            @else
                                <span class="text-success-600 font-medium">{{ __('shop.product.in_stock', ['count' => $product->stock_quantity]) }}</span>
                            @endif
                        </dd>
                    </div>
                </dl>

                @if ($product->isAvailable())
                    <div class="mt-6 flex items-center gap-3">
                        <div class="flex items-center border border-gray-200 rounded-xl">
                            <button type="button" wire:click="$set('quantity', {{ max(1, $quantity - 1) }})" class="h-11 w-11 flex items-center justify-center text-gray-500 hover:text-gray-900">−</button>
                            <span class="w-10 text-center font-semibold">{{ $quantity }}</span>
                            <button type="button" wire:click="$set('quantity', {{ min($product->stock_quantity, $quantity + 1) }})" class="h-11 w-11 flex items-center justify-center text-gray-500 hover:text-gray-900">+</button>
                        </div>

                        <button type="button" wire:click="addToCart" wire:key="add-to-cart-btn"
                            class="flex-1 py-3 rounded-xl bg-brand text-white font-semibold hover:bg-brand-hover transition-colors">
                            {{ __('shop.product.add_to_cart') }}
                        </button>
                    </div>
                    @error('quantity') <p class="text-sm text-danger-600 mt-2">{{ $message }}</p> @enderror
                @else
                    <button type="button" disabled class="mt-6 w-full py-3 rounded-xl bg-gray-100 text-gray-400 font-semibold cursor-not-allowed">
                        {{ __('shop.product.out_of_stock') }}
                    </button>
                @endif
            </div>
        </div>

        {{-- Tabs --}}
        <div class="mt-14">
            <div class="border-b border-gray-200 flex gap-6">
                @foreach (['overview', 'description', 'reviews'] as $tab)
                    <button type="button" wire:click="setTab('{{ $tab }}')"
                        class="pb-3 text-sm font-semibold border-b-2 -mb-px transition-colors {{ $activeTab === $tab ? 'border-brand text-brand' : 'border-transparent text-gray-400 hover:text-gray-600' }}">
                        {{ $tab === 'reviews' ? __('shop.product.tabs.reviews', ['count' => $product->reviews_count]) : __('shop.product.tabs.' . $tab) }}
                    </button>
                @endforeach
            </div>

            <div class="py-8 max-w-3xl">
                @if ($activeTab === 'overview')
                    <p class="text-gray-600 leading-relaxed">{{ Str::limit(strip_tags((string) $product->display_description), 280) }}</p>
                @elseif ($activeTab === 'description')
                    <div class="text-gray-600 leading-relaxed whitespace-pre-line">{{ $product->display_description ?: __('shop.product.tabs.description') }}</div>
                @else
                    <div class="space-y-6">
                        @forelse ($reviews as $review)
                            <div class="border-b border-gray-100 pb-5" wire:key="review-{{ $review->id }}">
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold text-gray-900 text-sm">{{ $review->customer->display_name }}</span>
                                    <span class="text-amber-500 text-xs">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                                </div>
                                @if ($review->title)
                                    <p class="font-medium text-gray-800 text-sm mt-1">{{ $review->title }}</p>
                                @endif
                                <p class="text-sm text-gray-600 mt-1">{{ $review->body }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-400">{{ __('shop.product.no_reviews') }}</p>
                        @endforelse

                        {{-- Write a review --}}
                        <div class="mt-8 bg-gray-50 rounded-2xl p-6">
                            <h3 class="font-semibold text-gray-900">{{ __('shop.product.write_review') }}</h3>

                            @if ($reviewSubmitted)
                                <p class="text-sm text-success-600 mt-3">{{ __('shop.product.review_submitted') }}</p>
                            @else
                                <p class="text-xs text-gray-400 mt-1">{{ __('shop.product.review_form.intro') }}</p>

                                <form wire:submit="submitReview" class="mt-4 space-y-3">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <input type="text" wire:model="reviewOrderNumber" placeholder="{{ __('shop.product.review_form.order_number') }}"
                                                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                                            @error('reviewOrderNumber') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <input type="email" wire:model="reviewEmail" placeholder="{{ __('shop.product.review_form.email') }}"
                                                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                                            @error('reviewEmail') <p class="text-xs text-danger-600 mt-1">{{ $message }}</p> @enderror
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-1" x-data>
                                        @for ($i = 1; $i <= 5; $i++)
                                            <button type="button" wire:click="$set('reviewRating', {{ $i }})"
                                                class="text-2xl {{ $reviewRating >= $i ? 'text-amber-500' : 'text-gray-300' }}">★</button>
                                        @endfor
                                    </div>

                                    <input type="text" wire:model="reviewTitle" placeholder="{{ __('shop.product.review_form.title') }}"
                                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">

                                    <textarea wire:model="reviewBody" rows="3" placeholder="{{ __('shop.product.review_form.body') }}"
                                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm"></textarea>
                                    @error('reviewBody') <p class="text-xs text-danger-600">{{ $message }}</p> @enderror

                                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-brand text-white text-sm font-semibold hover:bg-brand-hover transition-colors">
                                        {{ __('shop.product.review_form.submit') }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Related products --}}
        @if ($related->isNotEmpty())
            <div class="mt-16">
                <h2 class="text-xl font-bold text-gray-900 mb-6">{{ __('shop.product.related') }}</h2>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-5">
                    @foreach ($related as $item)
                        <a href="{{ route('shop.product.show', $item->slug) }}" wire:key="related-{{ $item->id }}"
                            class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg transition-shadow overflow-hidden flex flex-col">
                            <div class="aspect-square bg-gray-100 overflow-hidden">
                                @if ($item->image_url)
                                    <img src="{{ $item->image_url }}" alt="{{ $item->display_name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-4xl">📦</div>
                                @endif
                            </div>
                            <div class="p-3">
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ $item->display_name }}</p>
                                <p class="text-sm font-bold text-gray-900 mt-1">@include('partials.currency-amount', ['amount' => (float) $item->price])</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </main>

    @include('shop.partials.footer')
</div>

@script
<script>
    $wire.on('added-to-cart', () => {
        requestAnimationFrame(() => {
            if (!window.gsap) return;
            gsap.fromTo('[wire\\:key="add-to-cart-btn"]', { scale: 1 }, { scale: 1.05, duration: 0.15, yoyo: true, repeat: 1, ease: 'power1.inOut' });
        });
    });
</script>
@endscript
