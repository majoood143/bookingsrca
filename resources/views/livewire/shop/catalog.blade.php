@php
    use App\Models\BookingSetting;

    $locale = app()->getLocale();
    $primaryColor = BookingSetting::get('primary_color', '#05602b');
    $secondaryColor = BookingSetting::get('secondary_color', '#0da74c');
@endphp
<div dir="{{ $locale === 'ar' ? 'rtl' : 'ltr' }}" style="--color-brand: {{ $primaryColor }}; --color-brand-hover: {{ $secondaryColor }};">
    @push('meta')
        <meta name="description" content="{{ __('shop.catalog.subheading') }}">
        {{-- Filtered/searched views canonicalise to the plain catalog. --}}
        <link rel="canonical" href="{{ route('shop.index') }}">
    @endpush
    @include('shop.partials.header')

    <div class="bg-gradient-to-r from-brand to-brand-hover text-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-10 sm:py-14 text-center">
            <h1 class="text-2xl sm:text-3xl font-bold">{{ __('shop.catalog.heading') }}</h1>
            <p class="mt-2 text-white/90">{{ __('shop.catalog.subheading') }}</p>
        </div>
    </div>

    <main class="max-w-6xl mx-auto px-4 sm:px-6 py-10">
        {{-- Filters --}}
        <div class="flex flex-col sm:flex-row gap-3 mb-8">
            <div class="relative flex-1">
                <x-heroicon-o-magnifying-glass class="w-4 h-4 absolute top-1/2 -translate-y-1/2 {{ $locale === 'ar' ? 'right-3' : 'left-3' }} text-gray-400" />
                <input type="search" wire:model.live.debounce.400ms="search"
                    placeholder="{{ __('shop.catalog.search_placeholder') }}"
                    class="w-full rounded-xl border border-gray-200 py-2.5 {{ $locale === 'ar' ? 'pr-10 pl-4' : 'pl-10 pr-4' }} text-sm focus:ring-2 focus:ring-brand focus:border-brand">
            </div>

            <select wire:model.live="categoryId" class="rounded-xl border border-gray-200 py-2.5 px-3 text-sm focus:ring-2 focus:ring-brand focus:border-brand">
                <option value="">{{ __('shop.catalog.all_categories') }}</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->full_name }}</option>
                @endforeach
            </select>

            <select wire:model.live="brandId" class="rounded-xl border border-gray-200 py-2.5 px-3 text-sm focus:ring-2 focus:ring-brand focus:border-brand">
                <option value="">{{ __('shop.catalog.all_brands') }}</option>
                @foreach ($brands as $brand)
                    <option value="{{ $brand->id }}">{{ $brand->display_name }}</option>
                @endforeach
            </select>

            <select wire:model.live="sort" class="rounded-xl border border-gray-200 py-2.5 px-3 text-sm focus:ring-2 focus:ring-brand focus:border-brand">
                <option value="newest">{{ __('shop.catalog.sort.newest') }}</option>
                <option value="price_asc">{{ __('shop.catalog.sort.price_asc') }}</option>
                <option value="price_desc">{{ __('shop.catalog.sort.price_desc') }}</option>
                <option value="name">{{ __('shop.catalog.sort.name') }}</option>
            </select>
        </div>

        {{-- Product grid --}}
        @if ($products->isEmpty())
            <div class="text-center py-16">
                <div class="text-6xl mb-4">🛍️</div>
                <p class="text-gray-500">{{ __('shop.catalog.empty_state') }}</p>
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5">
                @foreach ($products as $product)
                    <div wire:key="product-{{ $product->id }}"
                        class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg transition-shadow overflow-hidden flex flex-col">
                        <a href="{{ route('shop.product.show', $product->slug) }}" class="block relative aspect-square bg-gray-100 overflow-hidden">
                            @if ($product->image_url)
                                <img src="{{ $product->image_url }}" alt="{{ $product->display_name }}"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-4xl">📦</div>
                            @endif

                            @if (!$product->isAvailable())
                                <span class="absolute top-2 {{ $locale === 'ar' ? 'left-2' : 'right-2' }} bg-gray-900/80 text-white text-[11px] font-semibold px-2.5 py-1 rounded-full">
                                    {{ __('shop.catalog.out_of_stock') }}
                                </span>
                            @endif
                        </a>

                        <div class="p-3.5 flex flex-col grow">
                            @if ($product->brand)
                                <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide">{{ $product->brand->display_name }}</span>
                            @endif
                            <a href="{{ route('shop.product.show', $product->slug) }}" class="font-semibold text-sm text-gray-900 line-clamp-2 mt-0.5">
                                {{ $product->display_name }}
                            </a>

                            <div class="mt-2 pt-2 border-t border-gray-100 flex items-center justify-between gap-2">
                                <span class="text-sm font-bold text-gray-900">@include('partials.currency-amount', ['amount' => (float) $product->price])</span>

                                <button type="button" wire:click="addToCart({{ $product->id }})" @click="$dispatch('cart-bump')"
                                    @disabled(!$product->isAvailable())
                                    class="inline-flex items-center justify-center h-8 w-8 rounded-lg text-white transition-colors disabled:bg-gray-200 disabled:cursor-not-allowed {{ $product->isAvailable() ? 'bg-brand hover:bg-brand-hover' : '' }}"
                                    aria-label="{{ __('shop.catalog.add_to_cart') }}">
                                    <x-heroicon-o-shopping-cart class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-10">
                {{ $products->links() }}
            </div>
        @endif
    </main>

    @include('shop.partials.footer')
</div>
