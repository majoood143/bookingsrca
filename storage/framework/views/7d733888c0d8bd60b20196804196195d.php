        
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
                            <?php
                                $d = \Carbon\Carbon::parse($date)->locale(app()->getLocale());
                                $isSoldOut = $soldOutDates->contains($date);
                            ?>
                            <button wire:key="date-<?php echo e($date); ?>"
                                wire:click="<?php echo e($isSoldOut ? '' : "selectDate('{$date}')"); ?>"
                                <?php echo e($isSoldOut ? 'disabled' : ''); ?>

                                class="group relative p-4 border-2 rounded-2xl text-center transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-brand-hover overflow-hidden
                                    <?php echo e($isSoldOut
                                        ? 'border-gray-100 bg-gray-50 cursor-not-allowed'
                                        : ($selectedDate === $date
                                            ? 'border-green-600 bg-green-600 shadow-md shadow-green-600'
                                            : 'border-gray-200 bg-white hover:border-green-300 hover:shadow-sm')); ?>">
                                <div
                                    class="text-xs font-bold uppercase tracking-widest
                                    <?php echo e($isSoldOut ? 'text-gray-300' : ($selectedDate === $date ? 'text-brand-hover/70' : 'text-gray-400')); ?>">
                                    <?php echo e($d->translatedFormat('D')); ?>

                                </div>
                                <div
                                    class="text-4xl font-black my-1
                                    <?php echo e($isSoldOut ? 'text-gray-300' : ($selectedDate === $date ? 'text-brand-hover' : 'text-gray-800')); ?>">
                                    <?php echo e($d->format('d')); ?>

                                </div>
                                <div
                                    class="text-sm font-semibold
                                    <?php echo e($isSoldOut ? 'text-gray-300' : ($selectedDate === $date ? 'text-brand-hover' : 'text-gray-500')); ?>">
                                    <?php echo e($d->translatedFormat('M Y')); ?>

                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedDate === $date && !$isSoldOut): ?>
                                    <div class="mt-2">
                                        <span class="inline-block w-2 h-2 rounded-full bg-brand-hover"></span>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isSoldOut): ?>
                                    <div class="pointer-events-none absolute inset-0 flex items-center justify-center bg-gray-50/70">
                                        <span
                                            class="-rotate-12 text-[10px] sm:text-xs font-black uppercase tracking-wider text-red-500/90 border-2 border-red-400/80 rounded-md px-2 py-0.5 bg-white/80">
                                            <?php echo e(__('event_booking.step1.sold_out')); ?>

                                        </span>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </button>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div wire:loading wire:target="selectDate"
                class="fixed inset-0 bg-white/60 flex items-center justify-center z-50">
                <svg class="animate-spin w-8 h-8 text-brand-hover" fill="none" viewBox="0 0 24 24">
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

                            class="w-full p-5 border-2 rounded-2xl text-left transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-brand-hover
                                <?php echo e($selectedSlot == $slot->id
                                    ? 'border-brand-hover bg-brand-hover/10 shadow-md shadow-brand-hover/20'
                                    : ($slotAvailable
                                        ? 'border-gray-200 bg-white hover:border-brand-hover/45 hover:shadow-sm cursor-pointer'
                                        : 'border-gray-100 bg-gray-50 cursor-not-allowed opacity-50')); ?>">
                            <div class="flex justify-between items-start mb-3">
                                <span class="text-xl font-bold text-gray-900"><?php echo e($showSlotEndTime ? $slot->getTimeRange() : $slot->start_time->format('H:i')); ?></span>
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
                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-gray-600 hover:text-gray-900 font-medium text-sm transition-colors border border-gray-200 rounded-xl hover:bg-gray-50 sm:w-auto">
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
                            <?php echo e($qty > 0 ? 'border-brand-hover bg-brand-hover/10 shadow-md shadow-brand-hover/20' : 'border-gray-200 bg-white'); ?>

                            <?php echo e(!$typeAvailable || $isBlocked ? 'opacity-60' : ''); ?>">

                            <div class="flex flex-wrap items-center gap-4">

                                
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h3 class="font-bold text-gray-900">
                                            <?php echo e($ticketType->getTranslation('name', app()->getLocale())); ?>

                                        </h3>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($qty > 0): ?>
                                            <span
                                                class="text-xs bg-brand-hover text-white px-2 py-0.5 rounded-full font-medium">
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
                                            <?php echo $__env->make('partials.currency-amount', ['amount' => $ticketType->price], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
                                        <div class="text-xs text-gray-400"><?php echo e(__('event_booking.step3.per_ticket')); ?>

                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>

                                
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($typeAvailable && !$isBlocked): ?>
                                    <div class="flex items-center gap-3 shrink-0">
                                        <button type="button" wire:click="decrementQuantity(<?php echo e($ticketType->id); ?>)"
                                            <?php echo e($qty <= 0 ? 'disabled' : ''); ?>

                                            class="w-10 h-10 rounded-full border-2 flex items-center justify-center text-xl font-bold transition-colors
                                                <?php echo e($qty <= 0 ? 'border-gray-200 text-gray-300 cursor-not-allowed' : 'border-gray-300 text-gray-600 hover:border-brand-hover hover:text-brand-hover'); ?>">
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
                                                <?php echo e($atMax ? 'border-gray-200 text-gray-300 cursor-not-allowed' : 'border-gray-300 text-gray-600 hover:border-brand-hover hover:text-brand-hover'); ?>">
                                            +
                                        </button>
                                    </div>

                                    
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($qty > 0): ?>
                                        <div class="text-right shrink-0 min-w-[80px]">
                                            <div class="text-sm font-semibold text-green-700">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ticketType->price > 0): ?><?php echo $__env->make('partials.currency-amount', ['amount' => $ticketType->price * $qty], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php else: ?><?php echo e(__('event_booking.step3.free')); ?><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
                        <span class="text-2xl font-black"><?php echo $__env->make('partials.currency-amount', ['amount' => $totalPrice], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></span>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="mt-6 flex items-center gap-4">
                    <button wire:click="previousStep"
                        class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-gray-600 hover:text-gray-900 font-medium text-sm transition-colors border border-gray-200 rounded-xl hover:bg-gray-50 sm:w-auto">
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
                                                    <?php echo e($selectedCount > 0 ? 'border-brand-hover bg-brand-hover/10 shadow-md shadow-brand-hover/20' : 'border-gray-200 bg-white'); ?>">
                                                <div class="flex items-start gap-4">
                                                    <div class="flex-1 min-w-0">
                                                        <h3 class="font-bold text-gray-900">
                                                            <?php echo e($service->getTranslation('name', app()->getLocale())); ?>

                                                        </h3>
                                                        <p class="text-sm text-gray-500 mt-0.5">
                                                            <?php echo e($service->getTranslation('description', app()->getLocale())); ?>

                                                        </p>
                                                        <p class="text-xs text-gray-400 mt-1">
                                                            <?php echo $__env->make('partials.currency-amount', ['amount' => $service->price], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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
                                                                    <?php echo e($selectedCount <= 0 ? 'border-gray-200 text-gray-300 cursor-not-allowed' : 'border-gray-300 text-gray-600 hover:border-brand-hover hover:text-brand-hover'); ?>">
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
                                                                    <?php echo e($selectedCount >= $qty ? 'border-gray-200 text-gray-300 cursor-not-allowed' : 'border-gray-300 text-gray-600 hover:border-brand-hover hover:text-brand-hover'); ?>">
                                                                +
                                                            </button>
                                                        </div>
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedCount > 0): ?>
                                                            <div class="text-xs text-brand-hover font-medium">
                                                                = <?php echo $__env->make('partials.currency-amount', ['amount' => $service->price * $selectedCount], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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
                    <span class="text-2xl font-black"><?php echo $__env->make('partials.currency-amount', ['amount' => $totalPrice], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></span>
                </div>

                <div class="mt-6 flex items-center gap-4">
                    <button wire:click="previousStep"
                        class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-gray-600 hover:text-gray-900 font-medium text-sm transition-colors border border-gray-200 rounded-xl hover:bg-gray-50 sm:w-auto">
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
                                <?php echo e($i === 0 ? 'border-brand-hover/30' : 'border-gray-200'); ?>">

                                
                                <div
                                    class="px-5 py-3 flex items-center gap-3 rounded-t-2xl
                                    <?php echo e($i === 0 ? 'bg-brand-hover/10 border-b border-brand-hover/30' : 'bg-gray-50 border-b border-gray-200'); ?>">
                                    <div
                                        class="w-7 h-7 rounded-full flex items-center justify-center text-sm font-bold shrink-0
                                        <?php echo e($i === 0 ? 'bg-brand-hover text-white' : 'bg-gray-400 text-white'); ?>">
                                        <?php echo e($i + 1); ?>

                                    </div>
                                    <span class="font-semibold text-gray-800">
                                        <?php echo e(__('event_booking.step5.attendee_n', ['n' => $i + 1])); ?>

                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($i === 0): ?>
                                            <span
                                                class="text-xs text-brand-hover font-normal ml-1">(<?php echo e(__('event_booking.step5.primary')); ?>)</span>
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
                                                class="w-full px-4 py-2.5 border rounded-xl text-gray-900 placeholder-gray-300 focus:ring-2 focus:ring-brand-hover focus:border-brand-hover outline-none transition
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
                                                class="w-full px-4 py-2.5 border rounded-xl text-gray-900 placeholder-gray-300 focus:ring-2 focus:ring-brand-hover focus:border-brand-hover outline-none transition
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
                                                <?php echo e(__('event_booking.step5.email_address')); ?>

                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($requireEmail): ?>
                                                    <span class="text-red-500">*</span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </label>
                                            <input type="email"
                                                wire:model.live="attendees.<?php echo e($i); ?>.email"
                                                placeholder="john@example.com"
                                                <?php echo e($copyContactToAll && $i > 0 ? 'readonly' : ''); ?>

                                                class="w-full px-4 py-2.5 border rounded-xl text-gray-900 placeholder-gray-300 focus:ring-2 focus:ring-brand-hover focus:border-brand-hover outline-none transition
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
                                                <?php echo e(__('event_booking.step5.phone_number')); ?>

                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($requirePhone): ?>
                                                    <span class="text-red-500">*</span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </label>
                                            <input type="tel" pattern="\+?\d{7,15}" inputmode="numeric"
                                                autocomplete="tel"
                                                wire:model.live="attendees.<?php echo e($i); ?>.phone"
                                                placeholder="+968 00000000"
                                                <?php echo e($copyContactToAll && $i > 0 ? 'readonly' : ''); ?>

                                                class="w-full px-4 py-2.5 border rounded-xl text-gray-900 placeholder-gray-300 focus:ring-2 focus:ring-brand-hover focus:border-brand-hover outline-none transition
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
                                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                                                        <?php echo e(__('event_booking.step5.date_of_birth')); ?>

                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($requireDateOfBirth): ?>
                                                            <span class="text-red-500">*</span>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </label>
                                                    <input type="date" dir="ltr"
                                                        wire:model="attendees.<?php echo e($i); ?>.date_of_birth"
                                                        min="<?php echo e($minBirthDate); ?>" max="<?php echo e($maxBirthDate); ?>"
                                                        class="w-full px-4 py-2.5 border rounded-xl text-gray-900 text-left focus:ring-2 focus:ring-brand-hover focus:border-brand-hover outline-none transition
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
                                                        <?php echo e(__('event_booking.step5.gender')); ?>

                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($requireGender): ?>
                                                            <span class="text-red-500">*</span>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </label>
                                                    <select wire:model="attendees.<?php echo e($i); ?>.gender"
                                                        class="w-full px-4 py-2.5 border rounded-xl text-gray-900 focus:ring-2 focus:ring-brand-hover focus:border-brand-hover outline-none transition
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
                                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                                                        <?php echo e(__('event_booking.step5.nationality')); ?>

                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($requireNationality): ?>
                                                            <span class="text-red-500">*</span>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </label>
                                                    <input type="text" x-model="search" @focus="open = true"
                                                        @input="open = true" @keydown.escape="open = false"
                                                        @click.outside="open = false" autocomplete="off"
                                                        placeholder="<?php echo e(__('booking.placeholders.nationality')); ?>"
                                                        class="w-full px-4 py-2.5 border rounded-xl text-gray-900 placeholder-gray-300 focus:ring-2 focus:ring-brand-hover focus:border-brand-hover outline-none transition
                                                            <?php echo e($errors->has("attendees.$i.nationality") ? 'border-red-400 bg-red-50' : 'border-gray-300'); ?>">
                                                    <div x-show="open" x-transition style="display: none;"
                                                        class="absolute z-30 mt-1 w-full max-h-56 overflow-y-auto bg-white border border-gray-200 rounded-xl shadow-lg">
                                                        <template x-for="[code, label] in Object.entries(filtered)"
                                                            :key="code">
                                                            <div @click="choose(code, label)"
                                                                class="px-4 py-2 text-sm text-gray-700 hover:bg-brand-hover/10 cursor-pointer"
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
                                                        <?php echo e(__('event_booking.step5.identity_number')); ?>

                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($requireIdentityNumber): ?>
                                                            <span class="text-red-500">*</span>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </label>
                                                    <input type="text"
                                                        wire:model="attendees.<?php echo e($i); ?>.identity_number"
                                                        placeholder="<?php echo e(__('booking.placeholders.identity_number')); ?>"
                                                        class="w-full px-4 py-2.5 border rounded-xl text-gray-900 placeholder-gray-300 focus:ring-2 focus:ring-brand-hover focus:border-brand-hover outline-none transition
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
                                    class="mt-0.5 h-4 w-4 rounded border-gray-300 text-brand-hover focus:ring-brand-hover cursor-pointer shrink-0
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
                                        <?php echo e($showSlotEndTime ? $timeSlots->find($selectedSlot)?->getTimeRange() : $timeSlots->find($selectedSlot)?->start_time->format('H:i')); ?></p>
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
                                                <span><?php echo $__env->make('partials.currency-amount', ['amount' => $ticketType->price], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> ×
                                                    <?php echo e($qty); ?></span>
                                                <span><?php echo $__env->make('partials.currency-amount', ['amount' => $ticketType->price * $qty], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></span>
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
                                                            class="shrink-0"><?php echo $__env->make('partials.currency-amount', ['amount' => $svc->price * $count], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></span>
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
                                    class="font-black text-2xl text-green-700"><?php echo $__env->make('partials.currency-amount', ['amount' => $totalPrice], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></span>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH /Users/majidalhajri/Downloads/booking/bookingrca/bookingsrca/resources/views/livewire/partials/booking-wizard-steps.blade.php ENDPATH**/ ?>