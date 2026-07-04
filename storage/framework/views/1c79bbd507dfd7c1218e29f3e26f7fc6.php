<div class="min-h-screen bg-gray-50" dir="<?php echo e(app()->getLocale() === 'ar' ? 'rtl' : 'ltr'); ?>">

    
    <div class="bg-gradient-to-r from-brand to-brand-hover text-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-8 sm:py-10">
            
            <div class="flex justify-end mb-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(app()->getLocale() === 'ar'): ?>
                    <a href="<?php echo e(route('lang.switch', 'en')); ?>"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-white/20 hover:bg-white/30 text-white rounded-full transition-colors">
                        🌐 <?php echo e(__('event_booking.switch_to_english')); ?>

                    </a>
                <?php else: ?>
                    <a href="<?php echo e(route('lang.switch', 'ar')); ?>"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-white/20 hover:bg-white/30 text-white rounded-full transition-colors">
                        🌐 <?php echo e(__('event_booking.switch_to_arabic')); ?>

                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="flex items-center justify-between gap-4">
                <h1 class="text-2xl sm:text-3xl font-bold leading-tight">
                    <?php echo e($event->getTranslation('title', app()->getLocale())); ?>

                </h1>
                <img src="<?php echo e(asset('storage/images/horizontalLogo-03.svg')); ?>" alt="Logo"
                    class="h-40 sm:h-40 w-auto shrink-0">
            </div>
            <p class="mt-2 text-blue-100 text-base sm:text-lg line-clamp-4">
                <?php echo e($event->getTranslation('description', app()->getLocale())); ?>

            </p>
            <div class="mt-3 flex flex-wrap gap-x-5 gap-y-1 text-sm text-blue-200">
                <span>📍 <?php echo e($event->getTranslation('location', app()->getLocale())); ?></span>
                <span>📅 <?php echo e($event->start_date->locale(app()->getLocale())->translatedFormat('M d')); ?> –
                    <?php echo e($event->end_date->locale(app()->getLocale())->translatedFormat('M d, Y')); ?></span>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($event->organizer): ?>
                    <span>👤 <?php echo e($event->organizer); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>

    
    <div class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-20">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-4">
            <div class="flex items-center">
                <?php
                    $steps = [
                        1 => __('event_booking.steps.date'),
                        2 => __('event_booking.steps.time'),
                        3 => __('event_booking.steps.tickets'),
                    ];
                    if ($extraServices->isNotEmpty()) {
                        $steps[4] = __('event_booking.steps.extras');
                    }
                    $steps[5] = __('event_booking.steps.details');
                    $steps[6] = __('event_booking.steps.payment');
                ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $num => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-center <?php echo e($num < 6 ? 'flex-1' : ''); ?>">
                        <div class="flex items-center gap-2 shrink-0">
                            <div
                                class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-colors
                                <?php echo e($step > $num
                                    ? 'bg-green-500 text-white'
                                    : ($step === $num
                                        ? 'bg-blue-600 text-white ring-4 ring-blue-100'
                                        : 'bg-gray-200 text-gray-500')); ?>">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step > $num): ?>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                <?php else: ?>
                                    <?php echo e($num); ?>

                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <span
                                class="hidden sm:block text-sm font-medium
                                <?php echo e($step >= $num ? 'text-gray-800' : 'text-gray-400'); ?>">
                                <?php echo e($label); ?>

                            </span>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($num < 6): ?>
                            <div
                                class="flex-1 h-0.5 mx-2 sm:mx-3 transition-colors
                                <?php echo e($step > $num ? 'bg-green-400' : 'bg-gray-200'); ?>">
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>

    
    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-8">

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('error')): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-start gap-3">
                <svg class="w-5 h-5 mt-0.5 shrink-0 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                        clip-rule="evenodd" />
                </svg>
                <p><?php echo e(session('error')); ?></p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step === 1): ?>
            <div wire:loading.class="opacity-50 pointer-events-none">
                <h2 class="text-2xl font-bold text-gray-900 mb-1"><?php echo e(__('event_booking.step1.heading')); ?></h2>
                <p class="text-gray-500 mb-6"><?php echo e(__('event_booking.step1.subheading')); ?></p>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($availableDates->isEmpty()): ?>
                    <div class="text-center py-16 bg-white rounded-2xl border border-gray-200">
                        <div class="text-5xl mb-4">📅</div>
                        <h3 class="text-lg font-semibold text-gray-700"><?php echo e(__('event_booking.step1.no_dates')); ?></h3>
                        <p class="text-gray-400 mt-1"><?php echo e(__('event_booking.step1.no_dates_body')); ?></p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $availableDates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $d = \Carbon\Carbon::parse($date)->locale(app()->getLocale()); ?>
                            <button wire:click="selectDate('<?php echo e($date); ?>')"
                                class="group p-4 border-2 rounded-2xl text-center transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-blue-500
                                    <?php echo e($selectedDate === $date
                                        ? 'border-blue-500 bg-blue-50 shadow-md shadow-blue-100'
                                        : 'border-gray-200 bg-white hover:border-blue-300 hover:shadow-sm'); ?>">
                                <div
                                    class="text-xs font-bold uppercase tracking-widest
                                    <?php echo e($selectedDate === $date ? 'text-blue-400' : 'text-gray-400'); ?>">
                                    <?php echo e($d->translatedFormat('D')); ?>

                                </div>
                                <div
                                    class="text-4xl font-black my-1
                                    <?php echo e($selectedDate === $date ? 'text-blue-600' : 'text-gray-800'); ?>">
                                    <?php echo e($d->format('d')); ?>

                                </div>
                                <div
                                    class="text-sm font-semibold
                                    <?php echo e($selectedDate === $date ? 'text-blue-500' : 'text-gray-500'); ?>">
                                    <?php echo e($d->translatedFormat('M Y')); ?>

                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedDate === $date): ?>
                                    <div class="mt-2">
                                        <span class="inline-block w-2 h-2 rounded-full bg-blue-500"></span>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </button>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div wire:loading wire:target="selectDate"
                class="fixed inset-0 bg-white/60 flex items-center justify-center z-50">
                <svg class="animate-spin w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step === 2): ?>
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-1"><?php echo e(__('event_booking.step2.heading')); ?></h2>
                <p class="text-gray-500 mb-6">
                    <?php echo e(__('event_booking.step2.subheading')); ?>

                    <strong
                        class="text-gray-700"><?php echo e(\Carbon\Carbon::parse($selectedDate)->locale(app()->getLocale())->translatedFormat('l, F j, Y')); ?></strong>
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" wire:loading.class="opacity-50 pointer-events-none">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $timeSlots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $slotAvailable = $slot->isAvailable();
                            $remaining = $slot->getRemainingCapacity();
                            $capacity = $slot->capacity ?? ($slot->max_capacity ?? 0);
                            $pct = $capacity > 0 ? min(100, round((($capacity - $remaining) / $capacity) * 100)) : 0;
                        ?>
                        <button wire:click="<?php echo e($slotAvailable ? 'selectSlot(' . $slot->id . ')' : ''); ?>"
                            <?php echo e(!$slotAvailable ? 'disabled' : ''); ?>

                            class="w-full p-5 border-2 rounded-2xl text-left transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-blue-500
                                <?php echo e($selectedSlot == $slot->id
                                    ? 'border-blue-500 bg-blue-50 shadow-md shadow-blue-100'
                                    : ($slotAvailable
                                        ? 'border-gray-200 bg-white hover:border-blue-300 hover:shadow-sm cursor-pointer'
                                        : 'border-gray-100 bg-gray-50 cursor-not-allowed opacity-50')); ?>">
                            <div class="flex justify-between items-start mb-3">
                                <span class="text-xl font-bold text-gray-900"><?php echo e($slot->getTimeRange()); ?></span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$slotAvailable): ?>
                                    <span
                                        class="text-xs bg-red-100 text-red-600 px-2.5 py-1 rounded-full font-semibold"><?php echo e(__('event_booking.step2.full')); ?></span>
                                <?php elseif($remaining <= 5): ?>
                                    <span
                                        class="text-xs bg-amber-100 text-amber-700 px-2.5 py-1 rounded-full font-semibold"><?php echo e(__('event_booking.step2.almost_full')); ?></span>
                                <?php else: ?>
                                    <span
                                        class="text-xs bg-green-100 text-green-700 px-2.5 py-1 rounded-full font-semibold"><?php echo e(__('event_booking.step2.available')); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <p class="text-sm text-gray-500 mb-2">
                                <?php echo e($remaining); ?> <?php echo e(__('event_booking.step2.spots_remaining')); ?>

                            </p>
                            <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all
                                    <?php echo e($pct > 80 ? 'bg-red-400' : ($pct > 50 ? 'bg-amber-400' : 'bg-green-400')); ?>"
                                    style="width: <?php echo e($pct); ?>%"></div>
                            </div>
                        </button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <button wire:click="previousStep"
                    class="mt-6 inline-flex items-center gap-1.5 text-gray-500 hover:text-gray-800 font-medium text-sm transition-colors">
                    ← <?php echo e(__('event_booking.back')); ?>

                </button>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step === 3): ?>
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-1"><?php echo e(__('event_booking.step3.heading')); ?></h2>
                <p class="text-gray-500 mb-6"><?php echo e(__('event_booking.step3.subheading')); ?></p>

                <div class="space-y-4" wire:loading.class="opacity-50 pointer-events-none">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $ticketTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticketType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $qty = $ticketQuantities[$ticketType->id] ?? 0;
                            $typeAvailable = $ticketType->isAvailable();
                            $typeRemaining = $ticketTypeRemaining[$ticketType->id] ?? 0;
                            $atTypeLimit = $qty >= $typeRemaining;
                            $dependencyIds = $ticketType->dependsOnMany->pluck('id')->all();
                            $isBlocked = !empty($dependencyIds)
                                && collect($dependencyIds)->every(fn ($id) => ($ticketQuantities[$id] ?? 0) <= 0);
                            $parentName = !empty($dependencyIds)
                                ? $ticketTypes->whereIn('id', $dependencyIds)
                                    ->map(fn ($t) => $t->getTranslation('name', app()->getLocale()))
                                    ->implode(', ')
                                : null;
                        ?>
                        <div
                            class="p-5 border-2 rounded-2xl transition-all duration-150
                            <?php echo e($qty > 0 ? 'border-blue-500 bg-blue-50 shadow-md shadow-blue-100' : 'border-gray-200 bg-white'); ?>

                            <?php echo e(!$typeAvailable || $isBlocked ? 'opacity-60' : ''); ?>">

                            <div class="flex flex-wrap items-center gap-4">

                                
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h3 class="font-bold text-gray-900">
                                            <?php echo e($ticketType->getTranslation('name', app()->getLocale())); ?>

                                        </h3>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($qty > 0): ?>
                                            <span
                                                class="text-xs bg-blue-600 text-white px-2 py-0.5 rounded-full font-medium">
                                                <?php echo e(__('event_booking.step3.n_selected', ['n' => $qty])); ?>

                                            </span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$typeAvailable): ?>
                                            <span
                                                class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full font-medium">
                                                <?php echo e(__('event_booking.step3.sold_out')); ?>

                                            </span>
                                        <?php elseif($typeRemaining <= 5): ?>
                                            <span
                                                class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full font-medium">
                                                <?php echo e($typeRemaining); ?> <?php echo e(__('event_booking.step3.tickets_available')); ?>

                                            </span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($parentName): ?>
                                            <span
                                                class="text-xs px-2 py-0.5 rounded-full font-medium
                                                <?php echo e($isBlocked ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700'); ?>">
                                                <?php echo e(__('event_booking.step3.requires_parent', ['parent' => $parentName])); ?>

                                            </span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ticketType->getTranslation('description', app()->getLocale())): ?>
                                        <p class="text-sm text-gray-500 mt-0.5">
                                            <?php echo e($ticketType->getTranslation('description', app()->getLocale())); ?>

                                        </p>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>

                                
                                <div class="text-right shrink-0">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ticketType->price <= 0): ?>
                                        <div class="text-2xl font-black text-green-600">
                                            <?php echo e(__('event_booking.step3.free')); ?></div>
                                    <?php else: ?>
                                        <div class="text-2xl font-black text-gray-900">
                                            OMR<?php echo e(number_format($ticketType->price, 3)); ?></div>
                                        <div class="text-xs text-gray-400"><?php echo e(__('event_booking.step3.per_ticket')); ?>

                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>

                                
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($typeAvailable && !$isBlocked): ?>
                                    <div class="flex items-center gap-3 shrink-0">
                                        <button type="button" wire:click="decrementQuantity(<?php echo e($ticketType->id); ?>)"
                                            <?php echo e($qty <= 0 ? 'disabled' : ''); ?>

                                            class="w-10 h-10 rounded-full border-2 flex items-center justify-center text-xl font-bold transition-colors
                                                <?php echo e($qty <= 0 ? 'border-gray-200 text-gray-300 cursor-not-allowed' : 'border-gray-300 text-gray-600 hover:border-blue-500 hover:text-blue-600'); ?>">
                                            −
                                        </button>
                                        <span
                                            class="text-2xl font-black text-gray-900 w-8 text-center tabular-nums"><?php echo e($qty); ?></span>
                                        <?php
                                            $atMax = $qty >= $maxTickets
                                                || array_sum($ticketQuantities) >= $maxTickets
                                                || array_sum($ticketQuantities) >= $slotRemainingCapacity
                                                || $atTypeLimit;
                                        ?>
                                        <button type="button" wire:click="incrementQuantity(<?php echo e($ticketType->id); ?>)"
                                            <?php echo e($atMax ? 'disabled' : ''); ?>

                                            class="w-10 h-10 rounded-full border-2 flex items-center justify-center text-xl font-bold transition-colors
                                                <?php echo e($atMax ? 'border-gray-200 text-gray-300 cursor-not-allowed' : 'border-gray-300 text-gray-600 hover:border-blue-500 hover:text-blue-600'); ?>">
                                            +
                                        </button>
                                    </div>

                                    
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($qty > 0): ?>
                                        <div class="text-right shrink-0 min-w-[80px]">
                                            <div class="text-sm font-semibold text-blue-700">
                                                <?php echo e($ticketType->price > 0 ? 'OMR' . number_format($ticketType->price * $qty, 3) : __('event_booking.step3.free')); ?>

                                            </div>
                                            <div class="text-xs text-gray-400">
                                                <?php echo e(__('event_booking.step3.subtotal')); ?></div>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php elseif($typeAvailable && $isBlocked): ?>
                                    
                                    <div class="flex items-center gap-2 text-amber-600 shrink-0">
                                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                        <span class="text-xs font-medium">
                                            <?php echo e(__('event_booking.step3.add_parent_first', ['parent' => $parentName])); ?>

                                        </span>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['ticketQuantities'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="mt-3 text-red-500 text-sm"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(array_sum($ticketQuantities) >= $slotRemainingCapacity): ?>
                    <p class="mt-3 text-amber-600 text-sm"><?php echo e(__('event_booking.step3.slot_limit_reached')); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                
                <?php $totalQty = array_sum($ticketQuantities); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($totalQty > 0): ?>
                    <div class="mt-6 p-4 sm:p-5 bg-gray-900 text-white rounded-2xl flex justify-between items-center">
                        <div>
                            <span
                                class="font-medium text-gray-300"><?php echo e(__('event_booking.step3.running_total')); ?></span>
                            <span
                                class="ml-2 text-xs text-gray-500"><?php echo e(__('event_booking.step3.n_tickets', ['n' => $totalQty])); ?></span>
                        </div>
                        <span class="text-2xl font-black">OMR<?php echo e(number_format($totalPrice, 3)); ?></span>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="mt-6 flex items-center gap-4">
                    <button wire:click="previousStep"
                        class="inline-flex items-center gap-1.5 text-gray-500 hover:text-gray-800 font-medium text-sm transition-colors">
                        ← <?php echo e(__('event_booking.back')); ?>

                    </button>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($totalQty >= $minTickets): ?>
                        <button wire:click="nextStep"
                            class="px-7 py-2.5 bg-brand text-white font-semibold rounded-xl hover:bg-brand-hover transition-colors shadow-sm">
                            <?php echo e(__('event_booking.continue')); ?> →
                        </button>
                    <?php else: ?>
                        <p class="text-sm text-gray-400">
                            <?php echo e(__('event_booking.step3.min_tickets', ['n' => $minTickets])); ?>

                        </p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step === 4): ?>
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-1"><?php echo e(__('event_booking.step4.heading')); ?></h2>
                <p class="text-gray-500 mb-6"><?php echo e(__('event_booking.step4.subheading')); ?></p>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($extraServices->isEmpty()): ?>
                    <div class="text-center py-14 bg-white rounded-2xl border border-gray-200 mb-6">
                        <div class="text-5xl mb-3">✨</div>
                        <p class="text-gray-500"><?php echo e(__('event_booking.step4.no_extras')); ?></p>
                    </div>
                <?php else: ?>
                    <div class="space-y-8" wire:loading.class="opacity-50 pointer-events-none">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $ticketTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticketType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $qty = $ticketQuantities[$ticketType->id] ?? 0; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($qty > 0): ?>
                                <div>
                                    
                                    <div class="flex items-center gap-3 mb-3">
                                        <div class="h-px flex-1 bg-gray-200"></div>
                                        <span
                                            class="text-sm font-bold text-gray-700 px-3 py-1 bg-gray-100 rounded-full">
                                            <?php echo e($ticketType->getTranslation('name', app()->getLocale())); ?>

                                            <span class="text-gray-400 font-normal ml-1">× <?php echo e($qty); ?></span>
                                        </span>
                                        <div class="h-px flex-1 bg-gray-200"></div>
                                    </div>

                                    <div class="space-y-3">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $extraServices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php $selectedCount = $ticketTypeServices[$ticketType->id][$service->id] ?? 0; ?>
                                            <div
                                                class="p-5 border-2 rounded-2xl transition-all duration-150
                                                    <?php echo e($selectedCount > 0 ? 'border-blue-500 bg-blue-50 shadow-md shadow-blue-100' : 'border-gray-200 bg-white'); ?>">
                                                <div class="flex items-start gap-4">
                                                    <div class="flex-1 min-w-0">
                                                        <h3 class="font-bold text-gray-900">
                                                            <?php echo e($service->getTranslation('name', app()->getLocale())); ?>

                                                        </h3>
                                                        <p class="text-sm text-gray-500 mt-0.5">
                                                            <?php echo e($service->getTranslation('description', app()->getLocale())); ?>

                                                        </p>
                                                        <p class="text-xs text-gray-400 mt-1">
                                                            OMR<?php echo e(number_format($service->price, 3)); ?>

                                                            <?php echo e(__('event_booking.step4.per_ticket')); ?>

                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($service->quantity_available): ?>
                                                                · <?php echo e($service->getRemainingQuantity()); ?>

                                                                <?php echo e(__('event_booking.step4.available')); ?>

                                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                        </p>
                                                    </div>

                                                    
                                                    <div class="shrink-0 flex flex-col items-end gap-1.5">
                                                        <div class="flex items-center gap-2">
                                                            <button type="button"
                                                                wire:click="decrementServiceQty(<?php echo e($ticketType->id); ?>, <?php echo e($service->id); ?>)"
                                                                <?php echo e($selectedCount <= 0 ? 'disabled' : ''); ?>

                                                                class="w-8 h-8 rounded-full border-2 flex items-center justify-center text-base font-bold transition-colors
                                                                    <?php echo e($selectedCount <= 0 ? 'border-gray-200 text-gray-300 cursor-not-allowed' : 'border-gray-300 text-gray-600 hover:border-blue-500 hover:text-blue-600'); ?>">
                                                                −
                                                            </button>
                                                            <span
                                                                class="text-base font-black text-gray-900 w-12 text-center tabular-nums">
                                                                <?php echo e($selectedCount); ?>/<?php echo e($qty); ?>

                                                            </span>
                                                            <button type="button"
                                                                wire:click="incrementServiceQty(<?php echo e($ticketType->id); ?>, <?php echo e($service->id); ?>)"
                                                                <?php echo e($selectedCount >= $qty ? 'disabled' : ''); ?>

                                                                class="w-8 h-8 rounded-full border-2 flex items-center justify-center text-base font-bold transition-colors
                                                                    <?php echo e($selectedCount >= $qty ? 'border-gray-200 text-gray-300 cursor-not-allowed' : 'border-gray-300 text-gray-600 hover:border-blue-500 hover:text-blue-600'); ?>">
                                                                +
                                                            </button>
                                                        </div>
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedCount > 0): ?>
                                                            <div class="text-xs text-blue-600 font-medium">
                                                                = OMR
                                                                <?php echo e(number_format($service->price * $selectedCount, 3)); ?>

                                                            </div>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                
                <div class="mt-6 p-4 sm:p-5 bg-gray-900 text-white rounded-2xl flex justify-between items-center">
                    <span class="font-medium text-gray-300"><?php echo e(__('event_booking.step3.running_total')); ?></span>
                    <span class="text-2xl font-black">OMR<?php echo e(number_format($totalPrice, 3)); ?></span>
                </div>

                <div class="mt-6 flex items-center gap-4">
                    <button wire:click="previousStep"
                        class="inline-flex items-center gap-1.5 text-gray-500 hover:text-gray-800 font-medium text-sm transition-colors">
                        ← <?php echo e(__('event_booking.back')); ?>

                    </button>
                    <button wire:click="nextStep"
                        class="px-7 py-2.5 bg-brand text-white font-semibold rounded-xl hover:bg-brand-hover transition-colors shadow-sm">
                        <?php echo e(__('event_booking.continue')); ?> →
                    </button>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step === 5): ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                
                <div class="lg:col-span-2">
                    <h2 class="text-2xl font-bold text-gray-900 mb-1"><?php echo e(__('event_booking.step5.heading')); ?></h2>
                    <p class="text-gray-500 mb-6"><?php echo e(__('event_booking.step5.subheading')); ?></p>

                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($attendees) > 1 && ($showEmail || $showPhone)): ?>
                        <div class="mb-6 p-4 bg-indigo-50 border border-indigo-200 rounded-xl flex items-start gap-3">
                            <input type="checkbox" wire:model.live="copyContactToAll" id="copyContactToAll"
                                class="mt-0.5 h-4 w-4 rounded border-indigo-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer shrink-0">
                            <label for="copyContactToAll" class="text-sm text-indigo-800 cursor-pointer leading-snug">
                                <span class="font-semibold"><?php echo e(__('event_booking.step5.copy_contact')); ?></span>
                                <span class="block text-indigo-500 text-xs mt-0.5">
                                    <?php echo e(__('event_booking.step5.email_label')); ?><?php echo e($showPhone ? ' & ' . __('event_booking.step5.phone_label') : ''); ?>

                                    <?php echo e(__('event_booking.step5.copy_contact_hint')); ?>

                                </span>
                            </label>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <form wire:submit.prevent="goToPaymentStep" class="space-y-6" novalidate>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $attendees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $attendee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $attendeeTicketType = $ticketTypes->find($attendee['ticket_type_id'] ?? null); ?>

                            <div
                                class="border-2 rounded-2xl
                                <?php echo e($i === 0 ? 'border-blue-200' : 'border-gray-200'); ?>">

                                
                                <div
                                    class="px-5 py-3 flex items-center gap-3 rounded-t-2xl
                                    <?php echo e($i === 0 ? 'bg-blue-50 border-b border-blue-200' : 'bg-gray-50 border-b border-gray-200'); ?>">
                                    <div
                                        class="w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold shrink-0
                                        <?php echo e($i === 0 ? 'bg-blue-600 text-white' : 'bg-gray-400 text-white'); ?>">
                                        <?php echo e($i + 1); ?>

                                    </div>
                                    <span class="font-semibold text-gray-800">
                                        <?php echo e(__('event_booking.step5.attendee_n', ['n' => $i + 1])); ?>

                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($i === 0): ?>
                                            <span
                                                class="text-xs text-blue-500 font-normal ml-1">(<?php echo e(__('event_booking.step5.primary')); ?>)</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </span>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attendeeTicketType): ?>
                                        <span
                                            class="text-xs bg-white border border-gray-300 text-gray-600 px-2 py-0.5 rounded-full ml-auto">
                                            <?php echo e($attendeeTicketType->getTranslation('name', app()->getLocale())); ?>

                                        </span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>

                                <div class="p-5 space-y-4">

                                    
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                                                <?php echo e(__('event_booking.step5.first_name')); ?> <span
                                                    class="text-red-500">*</span>
                                            </label>
                                            <input type="text"
                                                wire:model="attendees.<?php echo e($i); ?>.first_name"
                                                placeholder="<?php echo e(__('event_booking.step5.first_name_placeholder')); ?>"
                                                class="w-full px-4 py-2.5 border rounded-xl text-gray-900 placeholder-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition
                                                    <?php echo e($errors->has("attendees.$i.first_name") ? 'border-red-400 bg-red-50 ring-1 ring-red-300' : 'border-gray-300'); ?>">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ["attendees.$i.first_name"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                                                <?php echo e(__('event_booking.step5.last_name')); ?> <span
                                                    class="text-red-500">*</span>
                                            </label>
                                            <input type="text"
                                                wire:model="attendees.<?php echo e($i); ?>.last_name"
                                                placeholder="<?php echo e(__('event_booking.step5.last_name_placeholder')); ?>"
                                                class="w-full px-4 py-2.5 border rounded-xl text-gray-900 placeholder-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition
                                                    <?php echo e($errors->has("attendees.$i.last_name") ? 'border-red-400 bg-red-50 ring-1 ring-red-300' : 'border-gray-300'); ?>">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ["attendees.$i.last_name"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </div>

                                    
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showEmail): ?>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                                                <?php echo e(__('event_booking.step5.email_address')); ?> <span
                                                    class="text-red-500">*</span>
                                            </label>
                                            <input type="email"
                                                wire:model.live="attendees.<?php echo e($i); ?>.email"
                                                placeholder="john@example.com"
                                                <?php echo e($copyContactToAll && $i > 0 ? 'readonly' : ''); ?>

                                                class="w-full px-4 py-2.5 border rounded-xl text-gray-900 placeholder-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition
                                                    <?php echo e($copyContactToAll && $i > 0 ? 'bg-gray-50 text-gray-500 cursor-not-allowed' : ''); ?>

                                                    <?php echo e($errors->has("attendees.$i.email") ? 'border-red-400 bg-red-50 ring-1 ring-red-300' : 'border-gray-300'); ?>">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($i === 0): ?>
                                                <p class="text-xs text-gray-400 mt-1.5">
                                                    <?php echo e(__('event_booking.step5.email_hint')); ?></p>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ["attendees.$i.email"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                    
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPhone): ?>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                                                <?php echo e(__('event_booking.step5.phone_number')); ?> <span
                                                    class="text-red-500">*</span>
                                            </label>
                                            <input type="tel" pattern="\+?\d{7,15}" inputmode="numeric"
                                                autocomplete="tel"
                                                wire:model.live="attendees.<?php echo e($i); ?>.phone"
                                                placeholder="+968 00000000"
                                                <?php echo e($copyContactToAll && $i > 0 ? 'readonly' : ''); ?>

                                                class="w-full px-4 py-2.5 border rounded-xl text-gray-900 placeholder-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition
                                                    <?php echo e($copyContactToAll && $i > 0 ? 'bg-gray-50 text-gray-500 cursor-not-allowed' : ''); ?>

                                                    <?php echo e($errors->has("attendees.$i.phone") ? 'border-red-400 bg-red-50 ring-1 ring-red-300' : 'border-gray-300'); ?>">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ["attendees.$i.phone"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                <p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p>
                                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                    
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showDateOfBirth || $showGender || $showNationality || $showIdentityNumber): ?>
                                        <?php
                                            $optCount = collect([
                                                $showDateOfBirth,
                                                $showGender,
                                                $showNationality,
                                                $showIdentityNumber,
                                            ])
                                                ->filter()
                                                ->count();
                                        ?>
                                        <div class="grid grid-cols-1 sm:grid-cols-<?php echo e($optCount); ?> gap-4">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showDateOfBirth): ?>
                                                <div>
                                                    <label
                                                        class="block text-sm font-semibold text-gray-700 mb-1.5"><?php echo e(__('event_booking.step5.date_of_birth')); ?></label>
                                                    <input type="date" dir="ltr"
                                                        wire:model="attendees.<?php echo e($i); ?>.date_of_birth"
                                                        min="<?php echo e($minBirthDate); ?>" max="<?php echo e($maxBirthDate); ?>"
                                                        class="w-full px-4 py-2.5 border rounded-xl text-gray-900 text-left focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition
                                                            <?php echo e($errors->has("attendees.$i.date_of_birth") ? 'border-red-400 bg-red-50' : 'border-gray-300'); ?>">
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ["attendees.$i.date_of_birth"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                        <p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p>
                                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showGender): ?>
                                                <div>
                                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                                                        <?php echo e(__('event_booking.step5.gender')); ?> <span
                                                            class="text-red-500">*</span>
                                                    </label>
                                                    <select wire:model="attendees.<?php echo e($i); ?>.gender"
                                                        class="w-full px-4 py-2.5 border rounded-xl text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition
                                                            <?php echo e($errors->has("attendees.$i.gender") ? 'border-red-400 bg-red-50' : 'border-gray-300'); ?>">
                                                        <option value="">
                                                            <?php echo e(__('event_booking.step5.select_gender')); ?></option>
                                                        <option value="male"><?php echo e(__('event_booking.step5.male')); ?>

                                                        </option>
                                                        <option value="female"><?php echo e(__('event_booking.step5.female')); ?>

                                                        </option>
                                                    </select>
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ["attendees.$i.gender"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                        <p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p>
                                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showNationality): ?>
                                                <div class="relative" x-data="{
                                                    open: false,
                                                    search: '',
                                                    options: <?php echo \Illuminate\Support\Js::from(__('booking.options.nationality'))->toHtml() ?>,
                                                    get filtered() {
                                                        if (!this.search.trim()) return this.options;
                                                        const term = this.search.toLowerCase();
                                                        return Object.fromEntries(Object.entries(this.options).filter(([code, label]) => label.toLowerCase().includes(term)));
                                                    },
                                                    choose(code, label) {
                                                        this.search = label;
                                                        this.open = false;
                                                        $wire.set('attendees.<?php echo e($i); ?>.nationality', code);
                                                    },
                                                }"
                                                    x-init="search = options[$wire.attendees[<?php echo e($i); ?>].nationality] ?? ''">
                                                    <label
                                                        class="block text-sm font-semibold text-gray-700 mb-1.5"><?php echo e(__('event_booking.step5.nationality')); ?></label>
                                                    <input type="text" x-model="search" @focus="open = true"
                                                        @input="open = true" @keydown.escape="open = false"
                                                        @click.outside="open = false" autocomplete="off"
                                                        placeholder="<?php echo e(__('booking.placeholders.nationality')); ?>"
                                                        class="w-full px-4 py-2.5 border rounded-xl text-gray-900 placeholder-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition
                                                            <?php echo e($errors->has("attendees.$i.nationality") ? 'border-red-400 bg-red-50' : 'border-gray-300'); ?>">
                                                    <div x-show="open" x-transition style="display: none;"
                                                        class="absolute z-30 mt-1 w-full max-h-56 overflow-y-auto bg-white border border-gray-200 rounded-xl shadow-lg">
                                                        <template x-for="[code, label] in Object.entries(filtered)"
                                                            :key="code">
                                                            <div @click="choose(code, label)"
                                                                class="px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 cursor-pointer"
                                                                x-text="label"></div>
                                                        </template>
                                                        <div x-show="Object.keys(filtered).length === 0"
                                                            class="px-4 py-2 text-sm text-gray-400">
                                                            <?php echo e(__('event_booking.step5.nationality_no_results')); ?>

                                                        </div>
                                                    </div>
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ["attendees.$i.nationality"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                        <p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p>
                                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showIdentityNumber): ?>
                                                <div>
                                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                                                        <?php echo e(__('event_booking.step5.identity_number')); ?> <span
                                                            class="text-red-500">*</span>
                                                    </label>
                                                    <input type="text"
                                                        wire:model="attendees.<?php echo e($i); ?>.identity_number"
                                                        placeholder="<?php echo e(__('booking.placeholders.identity_number')); ?>"
                                                        class="w-full px-4 py-2.5 border rounded-xl text-gray-900 placeholder-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition
                                                            <?php echo e($errors->has("attendees.$i.identity_number") ? 'border-red-400 bg-red-50' : 'border-gray-300'); ?>">
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ["attendees.$i.identity_number"];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                        <p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p>
                                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($termsEn || $termsAr): ?>
                            <div class="rounded-xl border border-gray-200 overflow-hidden" x-data="{ open: false }">
                                <button type="button"
                                    class="w-full flex items-center justify-between bg-gray-50 px-4 py-3 text-left focus:outline-none"
                                    @click="open = !open" :aria-expanded="open">
                                    <h4 class="font-semibold text-gray-800 text-sm">
                                        <?php echo e(__('event_booking.step5.terms_heading')); ?></h4>
                                    <svg class="w-4 h-4 text-gray-500 transition-transform duration-200"
                                        :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <div x-show="open" x-transition:enter="transition-all duration-200 ease-out"
                                    x-transition:enter-start="opacity-0 max-h-0"
                                    x-transition:enter-end="opacity-100 max-h-96"
                                    x-transition:leave="transition-all duration-150 ease-in"
                                    x-transition:leave-start="opacity-100 max-h-96"
                                    x-transition:leave-end="opacity-0 max-h-0"
                                    class="border-t border-gray-200 overflow-hidden">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(app()->getLocale() === 'ar' && $termsAr): ?>
                                        <div class="px-4 py-3">
                                            <div class="prose prose-sm max-w-none text-gray-600 max-h-48 overflow-y-auto text-sm leading-relaxed"
                                                dir="rtl">
                                                <?php echo $termsAr; ?>

                                            </div>
                                        </div>
                                    <?php elseif($termsEn): ?>
                                        <div class="px-4 py-3">
                                            <div class="prose prose-sm max-w-none text-gray-600 max-h-48 overflow-y-auto text-sm leading-relaxed"
                                                dir="ltr">
                                                <?php echo $termsEn; ?>

                                            </div>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <input type="checkbox" wire:model="agreedToTerms" id="agreedToTerms"
                                    class="mt-0.5 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer shrink-0
                                        <?php echo e($errors->has('agreedToTerms') ? 'border-red-400 ring-1 ring-red-300' : ''); ?>">
                                <label for="agreedToTerms" class="text-sm text-gray-700 cursor-pointer leading-snug">
                                    <?php echo e(__('event_booking.step5.terms_agree')); ?>

                                    <a href="<?php echo e(app()->getLocale() === 'ar' ? 'https://razatfarm.gov.om/terms-of-use/' : 'https://razatfarm.gov.om/en/terms-of-use/'); ?>"
                                        target="_blank" rel="noopener noreferrer"
                                        class="font-semibold text-gray-900 underline hover:text-brand"><?php echo e(__('event_booking.step5.terms_heading')); ?></a>
                                    <span class="text-red-500 ml-0.5">*</span>
                                </label>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['agreedToTerms'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="text-red-500 text-sm -mt-3"><?php echo e(__('event_booking.step5.terms_required')); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        
                        <div class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                            <button type="button" wire:click="previousStep"
                                class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-gray-600 hover:text-gray-900 font-medium text-sm transition-colors border border-gray-200 rounded-xl hover:bg-gray-50 sm:w-auto">
                                ← <?php echo e(__('event_booking.back')); ?>

                            </button>
                            <button type="submit"
                                class="flex-1 sm:flex-none px-8 py-3 bg-brand text-white font-bold text-base rounded-xl hover:bg-brand-hover transition-colors shadow-md shadow-brand/30 disabled:opacity-60 disabled:cursor-not-allowed"
                                wire:loading.attr="disabled" wire:target="goToPaymentStep">
                                <span wire:loading.remove wire:target="goToPaymentStep">
                                    <?php echo e(__('event_booking.step5.continue_to_payment')); ?> →
                                </span>
                                <span wire:loading wire:target="goToPaymentStep"
                                    class="inline-flex items-center gap-2">
                                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                    </svg>
                                    <?php echo e(__('event_booking.step5.validating')); ?>

                                </span>
                            </button>
                        </div>
                    </form>
                </div>

                
                <div class="lg:col-span-1">
                    <div
                        class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden lg:sticky lg:top-24">
                        <div class="bg-gradient-to-r from-brand to-brand-hover text-white px-5 py-4">
                            <h3 class="font-bold text-base"><?php echo e(__('event_booking.step5.booking_summary')); ?></h3>
                        </div>
                        <div class="p-5 space-y-4 text-sm">

                            <div>
                                <p class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-0.5">
                                    <?php echo e(__('event_booking.summary.event')); ?></p>
                                <p class="font-semibold text-gray-900 leading-tight">
                                    <?php echo e($event->getTranslation('title', app()->getLocale())); ?></p>
                            </div>

                            <div class="pt-3 border-t border-gray-100 grid grid-cols-2 gap-3">
                                <div>
                                    <p class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-0.5">
                                        <?php echo e(__('event_booking.summary.date')); ?></p>
                                    <p class="font-medium text-gray-800">
                                        <?php echo e(\Carbon\Carbon::parse($selectedDate)->locale(app()->getLocale())->translatedFormat('M d, Y')); ?>

                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-0.5">
                                        <?php echo e(__('event_booking.summary.time')); ?></p>
                                    <p class="font-medium text-gray-800">
                                        <?php echo e($timeSlots->find($selectedSlot)?->getTimeRange()); ?></p>
                                </div>
                            </div>



                            
                            <div class="pt-3 border-t border-gray-100">
                                <p class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-2">
                                    <?php echo e(__('event_booking.summary.tickets')); ?></p>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $ticketTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticketType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php $qty = $ticketQuantities[$ticketType->id] ?? 0; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($qty > 0): ?>
                                        <div class="flex justify-between items-center text-gray-700 mb-1">
                                            <span
                                                class="truncate pr-2"><?php echo e($ticketType->getTranslation('name', app()->getLocale())); ?></span>
                                            <span class="shrink-0 text-gray-400 text-xs">× <?php echo e($qty); ?></span>
                                        </div>
                                        <div class="flex justify-between text-gray-500 text-xs mb-2">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ticketType->price > 0): ?>
                                                <span>OMR<?php echo e(number_format($ticketType->price, 3)); ?> ×
                                                    <?php echo e($qty); ?></span>
                                                <span>OMR<?php echo e(number_format($ticketType->price * $qty, 3)); ?></span>
                                            <?php else: ?>
                                                <span><?php echo e(__('event_booking.step3.free')); ?></span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($attendees)): ?>
                                <div class="pt-3 border-t border-gray-100">
                                    <p class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-2">
                                        <?php echo e(__('event_booking.summary.attendees')); ?></p>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $attendees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attendee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php $attendeeTicketType = $ticketTypes->find($attendee['ticket_type_id'] ?? null); ?>
                                        <div class="flex justify-between items-center text-gray-700 mb-1">
                                            <span
                                                class="truncate pr-2"><?php echo e(trim(($attendee['first_name'] ?? '') . ' ' . ($attendee['last_name'] ?? '')) ?: '—'); ?></span>
                                            <span
                                                class="shrink-0 text-gray-400 text-xs truncate max-w-[40%]"><?php echo e($attendeeTicketType?->getTranslation('name', app()->getLocale())); ?></span>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            
                            <?php
                                $hasServices = collect($ticketTypeServices)->flatten()->sum() > 0;
                            ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasServices): ?>
                                <div class="pt-3 border-t border-gray-100">
                                    <p class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-2">
                                        <?php echo e(__('event_booking.summary.extra_services')); ?></p>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $ticketTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticketType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $qty = $ticketQuantities[$ticketType->id] ?? 0;
                                            $serviceCounts = $ticketTypeServices[$ticketType->id] ?? [];
                                        ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($qty > 0 && !empty($serviceCounts)): ?>
                                            <p class="text-xs text-gray-400 mb-1">
                                                <?php echo e($ticketType->getTranslation('name', app()->getLocale())); ?></p>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $serviceCounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $serviceId => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php $svc = $count > 0 ? $extraServices->find($serviceId) : null; ?>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($svc): ?>
                                                    <div class="flex justify-between text-gray-600 text-xs mb-1">
                                                        <span
                                                            class="truncate pr-2"><?php echo e($svc->getTranslation('name', app()->getLocale())); ?>

                                                            × <?php echo e($count); ?></span>
                                                        <span
                                                            class="shrink-0">OMR<?php echo e(number_format($svc->price * $count, 3)); ?></span>
                                                    </div>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <div class="pt-3 border-t-2 border-gray-200 flex justify-between items-baseline">
                                <span
                                    class="font-bold text-gray-900 text-base"><?php echo e(__('event_booking.summary.total')); ?></span>
                                <span
                                    class="font-black text-2xl text-blue-600">OMR<?php echo e(number_format($totalPrice, 3)); ?></span>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step === 6): ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                
                <div class="lg:col-span-2">
                    <h2 class="text-2xl font-bold text-gray-900 mb-1"><?php echo e(__('event_booking.step6.heading')); ?></h2>
                    <p class="text-gray-500 mb-6"><?php echo e(__('event_booking.step6.subheading')); ?></p>

                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeGateway === 'thawani'): ?>
                        <div
                            class="p-6 border-2 border-blue-500 bg-blue-50 rounded-2xl shadow-md shadow-blue-100 mb-6">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-sm border border-blue-100 shrink-0">
                                    
                                    <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900 text-lg">
                                        <?php echo e(__('event_booking.step6.thawani_title')); ?></p>
                                    <p class="text-sm text-gray-500"><?php echo e(__('event_booking.step6.thawani_subtitle')); ?>

                                    </p>
                                </div>
                                <div class="ml-auto">
                                    <span
                                        class="inline-block w-3 h-3 rounded-full bg-blue-500 ring-4 ring-blue-200"></span>
                                </div>
                            </div>
                            <p class="mt-4 text-sm text-blue-700 bg-blue-100 rounded-lg px-3 py-2">
                                <?php echo e(__('event_booking.step6.thawani_redirect_note')); ?>

                            </p>
                        </div>
                    <?php elseif($activeGateway === 'nbo'): ?>
                        <div
                            class="p-6 border-2 border-indigo-500 bg-indigo-50 rounded-2xl shadow-md shadow-indigo-100 mb-6">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-sm border border-indigo-100 shrink-0">
                                    <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900 text-lg">
                                        <?php echo e(__('event_booking.step6.nbo_title')); ?></p>
                                    <p class="text-sm text-gray-500"><?php echo e(__('event_booking.step6.nbo_subtitle')); ?></p>
                                </div>
                                <div class="ml-auto">
                                    <span
                                        class="inline-block w-3 h-3 rounded-full bg-indigo-500 ring-4 ring-indigo-200"></span>
                                </div>
                            </div>
                            <p class="mt-4 text-sm text-indigo-700 bg-indigo-100 rounded-lg px-3 py-2">
                                <?php echo e(__('event_booking.step6.nbo_redirect_note')); ?>

                            </p>
                        </div>
                    <?php elseif($activeGateway === 'cash'): ?>
                        <div class="p-6 border-2 border-amber-400 bg-amber-50 rounded-2xl mb-6">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-sm border border-amber-100 shrink-0">
                                    <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900 text-lg">
                                        <?php echo e(__('event_booking.step6.pay_at_door_title')); ?></p>
                                    <p class="text-sm text-gray-500">
                                        <?php echo e(__('event_booking.step6.pay_at_door_subtitle')); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        
                        <div class="p-6 border-2 border-green-400 bg-green-50 rounded-2xl mb-6">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-sm border border-green-100 shrink-0">
                                    <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900 text-lg">
                                        <?php echo e(__('event_booking.step6.free_title')); ?></p>
                                    <p class="text-sm text-gray-500"><?php echo e(__('event_booking.step6.free_subtitle')); ?>

                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <div class="flex items-start gap-3">
                        <input type="checkbox" wire:model="agreedToTerms" id="agreedToTerms1"
                            class="mt-0.5 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer shrink-0
                                        <?php echo e($errors->has('agreedToTerms') ? 'border-red-400 ring-1 ring-red-300' : ''); ?>">
                        <label for="agreedToTerms" class="text-sm text-gray-700 cursor-pointer leading-snug">
                            <?php echo e(__('event_booking.step5.terms_agree')); ?>

                            <span
                                class="font-semibold text-gray-900"><?php echo e(__('event_booking.step5.terms_heading')); ?></span>
                            <span class="text-red-500 ml-0.5">*</span>
                        </label>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['agreedToTerms1'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-red-500 text-sm -mt-3"><?php echo e(__('event_booking.step5.terms_required')); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <button type="button" wire:click="previousStep"
                            class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-gray-600 hover:text-gray-900 font-medium text-sm transition-colors border border-gray-200 rounded-xl hover:bg-gray-50 sm:w-auto">
                            ← <?php echo e(__('event_booking.back')); ?>

                        </button>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeGateway === 'thawani'): ?>
                            <button type="button" wire:click="submitBooking"
                                class="flex-1 sm:flex-none px-8 py-3 bg-blue-600 text-white font-bold text-base rounded-xl hover:bg-blue-700 transition-colors shadow-md shadow-blue-200 disabled:opacity-60 disabled:cursor-not-allowed"
                                wire:loading.attr="disabled" wire:target="submitBooking">
                                <span wire:loading.remove wire:target="submitBooking">
                                    🔒 <?php echo e(__('event_booking.step6.pay_thawani_btn')); ?>

                                </span>
                                <span wire:loading wire:target="submitBooking" class="inline-flex items-center gap-2">
                                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                    </svg>
                                    <?php echo e(__('event_booking.step6.redirecting_thawani')); ?>

                                </span>
                            </button>
                        <?php elseif($activeGateway === 'nbo'): ?>
                            <button type="button" wire:click="submitBooking"
                                class="flex-1 sm:flex-none px-8 py-3 bg-indigo-600 text-white font-bold text-base rounded-xl hover:bg-indigo-700 transition-colors shadow-md shadow-indigo-200 disabled:opacity-60 disabled:cursor-not-allowed"
                                wire:loading.attr="disabled" wire:target="submitBooking">
                                <span wire:loading.remove wire:target="submitBooking">
                                    🔒 <?php echo e(__('event_booking.step6.pay_nbo_btn')); ?>

                                </span>
                                <span wire:loading wire:target="submitBooking" class="inline-flex items-center gap-2">
                                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                    </svg>
                                    <?php echo e(__('event_booking.step6.redirecting_nbo')); ?>

                                </span>
                            </button>
                        <?php else: ?>
                            <button type="button" wire:click="submitBooking"
                                class="flex-1 sm:flex-none px-8 py-3 bg-green-600 text-white font-bold text-base rounded-xl hover:bg-green-700 transition-colors shadow-md shadow-green-200 disabled:opacity-60 disabled:cursor-not-allowed"
                                wire:loading.attr="disabled" wire:target="submitBooking">
                                <span wire:loading.remove wire:target="submitBooking">
                                    ✓ <?php echo e(__('event_booking.step6.confirm_booking')); ?>

                                </span>
                                <span wire:loading wire:target="submitBooking" class="inline-flex items-center gap-2">
                                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                    </svg>
                                    <?php echo e(__('event_booking.step6.processing')); ?>

                                </span>
                            </button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                
                <div class="lg:col-span-1">
                    <div
                        class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden lg:sticky lg:top-24">
                        <div class="bg-gradient-to-r from-brand to-brand-hover text-white px-5 py-4">
                            <h3 class="font-bold text-base"><?php echo e(__('event_booking.step6.order_summary')); ?></h3>
                        </div>
                        <div class="p-5 space-y-4 text-sm">

                            <div>
                                <p class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-0.5">
                                    <?php echo e(__('event_booking.summary.event')); ?></p>
                                <p class="font-semibold text-gray-900 leading-tight">
                                    <?php echo e($event->getTranslation('title', app()->getLocale())); ?></p>
                            </div>

                            <div class="pt-3 border-t border-gray-100 grid grid-cols-2 gap-3">
                                <div>
                                    <p class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-0.5">
                                        <?php echo e(__('event_booking.summary.date')); ?></p>
                                    <p class="font-medium text-gray-800">
                                        <?php echo e(\Carbon\Carbon::parse($selectedDate)->locale(app()->getLocale())->translatedFormat('M d, Y')); ?>

                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-0.5">
                                        <?php echo e(__('event_booking.summary.time')); ?></p>
                                    <p class="font-medium text-gray-800">
                                        <?php echo e($timeSlots->find($selectedSlot)?->getTimeRange()); ?></p>
                                </div>
                            </div>

                            
                            <div class="pt-3 border-t border-gray-100">
                                <p class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-2">
                                    <?php echo e(__('event_booking.summary.tickets')); ?></p>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $ticketTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticketType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php $qty = $ticketQuantities[$ticketType->id] ?? 0; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($qty > 0): ?>
                                        <div class="flex justify-between text-gray-600 text-xs mb-1">
                                            <span
                                                class="truncate pr-2"><?php echo e($ticketType->getTranslation('name', app()->getLocale())); ?>

                                                × <?php echo e($qty); ?></span>
                                            <span
                                                class="shrink-0"><?php echo e($ticketType->price > 0 ? 'OMR' . number_format($ticketType->price * $qty, 3) : __('event_booking.step3.free')); ?></span>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($attendees)): ?>
                                <div class="pt-3 border-t border-gray-100">
                                    <p class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-2">
                                        <?php echo e(__('event_booking.summary.attendees')); ?></p>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $attendees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attendee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php $attendeeTicketType = $ticketTypes->find($attendee['ticket_type_id'] ?? null); ?>
                                        <div class="flex justify-between items-center text-gray-700 mb-1">
                                            <span
                                                class="truncate pr-2"><?php echo e(trim(($attendee['first_name'] ?? '') . ' ' . ($attendee['last_name'] ?? '')) ?: '—'); ?></span>
                                            <span
                                                class="shrink-0 text-gray-400 text-xs truncate max-w-[40%]"><?php echo e($attendeeTicketType?->getTranslation('name', app()->getLocale())); ?></span>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <?php
                                $hasServices = collect($ticketTypeServices)->flatten()->sum() > 0;
                            ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasServices): ?>
                                <div class="pt-3 border-t border-gray-100">
                                    <p class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-2">
                                        <?php echo e(__('event_booking.summary.extra_services')); ?></p>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $ticketTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticketType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $qty = $ticketQuantities[$ticketType->id] ?? 0;
                                            $serviceCounts = $ticketTypeServices[$ticketType->id] ?? [];
                                        ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($qty > 0 && !empty($serviceCounts)): ?>
                                            <p class="text-xs text-gray-400 mb-1">
                                                <?php echo e($ticketType->getTranslation('name', app()->getLocale())); ?></p>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $serviceCounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $serviceId => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php $svc = $count > 0 ? $extraServices->find($serviceId) : null; ?>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($svc): ?>
                                                    <div class="flex justify-between text-gray-600 text-xs mb-1">
                                                        <span
                                                            class="truncate pr-2"><?php echo e($svc->getTranslation('name', app()->getLocale())); ?>

                                                            × <?php echo e($count); ?></span>
                                                        <span
                                                            class="shrink-0">OMR<?php echo e(number_format($svc->price * $count, 3)); ?></span>
                                                    </div>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <div class="pt-3 border-t-2 border-gray-200 flex justify-between items-baseline">
                                <span
                                    class="font-bold text-gray-900 text-base"><?php echo e(__('event_booking.summary.total')); ?></span>
                                <span
                                    class="font-black text-2xl text-blue-600">OMR<?php echo e(number_format($totalPrice, 3)); ?></span>
                            </div>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeGateway === 'thawani'): ?>
                                <div class="pt-2 text-center text-xs text-gray-400">
                                    <svg class="inline w-4 h-4 mr-1 text-green-500" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <?php echo e(__('event_booking.step6.secured_by_thawani')); ?>

                                </div>
                            <?php elseif($activeGateway === 'nbo'): ?>
                                <div class="pt-2 text-center text-xs text-gray-400">
                                    <svg class="inline w-4 h-4 mr-1 text-green-500" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <?php echo e(__('event_booking.step6.secured_by_nbo')); ?>

                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        </div>
                    </div>
                </div>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    </div>
</div>
<?php /**PATH /Users/majidalhajri/Downloads/booking/bookingrca/bookingsrca/resources/views/livewire/event-booking.blade.php ENDPATH**/ ?>