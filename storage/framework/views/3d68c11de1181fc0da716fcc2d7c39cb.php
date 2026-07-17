<?php
    use App\Models\BookingSetting;

    $locale = app()->getLocale();
    $siteName = BookingSetting::get('site_name_' . $locale, config('app.name', 'Bookings'));
    $siteLogo = BookingSetting::get('site_logo');
    $primaryColor = BookingSetting::get('primary_color', '#05602b');
    $secondaryColor = BookingSetting::get('secondary_color', '#0da74c');
?>
<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', $locale)); ?>" dir="<?php echo e($locale === 'ar' ? 'rtl' : 'ltr'); ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($siteName); ?> — <?php echo e(__('home.meta_title')); ?></title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>

<body class="bg-gray-50 min-h-screen" style="--color-brand: <?php echo e($primaryColor); ?>; --color-brand-hover: <?php echo e($secondaryColor); ?>;">

    
    <header class="bg-white border-b border-gray-100 sticky top-0 z-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between gap-4">
            <a href="<?php echo e(url('/')); ?>" class="flex items-center gap-3 min-w-0">
                <img src="<?php echo e($siteLogo ? asset('storage/' . $siteLogo) : asset('storage/images/horizontalLogo-02.svg')); ?>"
                    alt="<?php echo e($siteName); ?>" class="h-10 w-auto shrink-0">
                <span class="font-bold text-lg text-gray-900 truncate"><?php echo e($siteName); ?></span>
            </a>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($locale === 'ar'): ?>
                <a href="<?php echo e(route('lang.switch', 'en')); ?>"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-full transition-colors shrink-0">
                    🌐 <?php echo e(__('event_booking.switch_to_english')); ?>

                </a>
            <?php else: ?>
                <a href="<?php echo e(route('lang.switch', 'ar')); ?>"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-full transition-colors shrink-0">
                    🌐 <?php echo e(__('event_booking.switch_to_arabic')); ?>

                </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </header>

    
    <div class="bg-gradient-to-r from-brand to-brand-hover text-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-14 sm:py-20 text-center">
            <h1 class="text-3xl sm:text-4xl font-bold leading-tight"><?php echo e(__('home.hero.heading')); ?></h1>
            <p class="mt-3 text-base sm:text-lg text-white/90 max-w-2xl mx-auto"><?php echo e(__('home.hero.subheading')); ?></p>
        </div>
    </div>

    
    <main class="max-w-6xl mx-auto px-4 sm:px-6 py-12 sm:py-16">
        <div class="text-center mb-10">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900"><?php echo e(__('home.section.heading')); ?></h2>
            <p class="mt-2 text-gray-500"><?php echo e(__('home.section.subheading')); ?></p>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($events->isEmpty()): ?>
            <div class="text-center py-16">
                <div class="text-6xl mb-4">🗓️</div>
                <h3 class="text-xl font-semibold text-gray-900"><?php echo e(__('home.empty_state.heading')); ?></h3>
                <p class="mt-2 text-gray-500"><?php echo e(__('home.empty_state.description')); ?></p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $remaining = max(0, $event->max_attendees - ($event->booked_quantity ?? 0));
                        $soldOut = $remaining <= 0;
                        $minPrice = $event->ticketTypes->where('is_active', true)->min('price');
                    ?>
                    <a href="<?php echo e(route('event.booking', $event->slug)); ?>"
                        class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg transition-shadow overflow-hidden flex flex-col">
                        <div class="relative aspect-video bg-gray-100 overflow-hidden">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($event->image): ?>
                                <img src="<?php echo e(asset('storage/' . $event->image)); ?>" alt="<?php echo e($event->title); ?>"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center text-5xl">🎟️</div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($soldOut): ?>
                                <span
                                    class="absolute top-3 <?php echo e($locale === 'ar' ? 'left-3' : 'right-3'); ?> bg-gray-900/80 text-white text-xs font-semibold px-3 py-1 rounded-full">
                                    <?php echo e(__('home.card.sold_out')); ?>

                                </span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div class="p-5 flex flex-col grow">
                            <h3 class="font-bold text-lg text-gray-900 line-clamp-2"><?php echo e($event->title); ?></h3>

                            <div class="mt-2 space-y-1 text-sm text-gray-500">
                                <div class="flex items-center gap-1.5">
                                    <span>📅</span>
                                    <span><?php echo e($event->start_date->locale($locale)->translatedFormat('M d')); ?> –
                                        <?php echo e($event->end_date->locale($locale)->translatedFormat('M d, Y')); ?></span>
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($event->location): ?>
                                    <div class="flex items-center gap-1.5 truncate">
                                        <span>📍</span>
                                        <span class="truncate"><?php echo e($event->location); ?></span>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between gap-3">
                                <div class="text-sm">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(is_null($minPrice)): ?>
                                        &nbsp;
                                    <?php elseif($minPrice <= 0): ?>
                                        <span class="font-bold text-gray-900"><?php echo e(__('home.card.free')); ?></span>
                                    <?php else: ?>
                                        <span class="text-gray-500"><?php echo e(__('home.card.from')); ?></span>
                                        <span class="font-bold text-gray-900"><?php echo $__env->make('partials.currency-amount', ['amount' => $minPrice], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>

                                <span
                                    class="inline-flex items-center gap-1 px-4 py-2 rounded-xl text-sm font-semibold transition-colors <?php echo e($soldOut ? 'bg-gray-100 text-gray-400' : 'bg-brand text-white group-hover:bg-brand-hover'); ?>">
                                    <?php echo e($soldOut ? __('home.card.sold_out') : __('home.card.book_now')); ?>

                                </span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </main>

    
    <footer class="border-t border-gray-100 py-8">
        <p class="text-center text-sm text-gray-400">© <?php echo e(now()->year); ?> <?php echo e($siteName); ?>. <?php echo e(__('home.footer.rights')); ?></p>
    </footer>

</body>

</html>
<?php /**PATH /Users/majidalhajri/Downloads/booking/bookingrca/bookingsrca/resources/views/welcome.blade.php ENDPATH**/ ?>