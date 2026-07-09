<?php
    $t = fn (string $key, array $params = []) => __('signage.' . $key, $params, $lang);
    $eventTitle = $event->getTranslation('title', $lang);
    $meetingPoint = $signage->getTranslation('meeting_point', $lang);
    $welcomeMessage = $signage->getTranslation('welcome_message', $lang) ?: $t('default_welcome_message');
    $dir = $lang === 'ar' ? 'rtl' : 'ltr';
?>

<div dir="<?php echo e($dir); ?>" class="flex flex-col min-h-screen p-4 sm:p-6 gap-4 sm:gap-6">

    
    <header class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="h-16 w-16 rounded-2xl bg-white/10 border border-white/20 backdrop-blur flex items-center justify-center overflow-hidden shrink-0">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($signage->logo_path): ?>
                    <img src="<?php echo e(asset('storage/' . $signage->logo_path)); ?>" alt="<?php echo e($eventTitle); ?>" class="h-full w-full object-contain p-1.5">
                <?php elseif($event->image): ?>
                    <img src="<?php echo e(asset('storage/' . $event->image)); ?>" alt="<?php echo e($eventTitle); ?>" class="h-full w-full object-cover">
                <?php else: ?>
                    <span class="text-2xl font-extrabold"><?php echo e(mb_substr($eventTitle, 0, 1)); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <h1 class="text-white/80 text-xl sm:text-3xl font-extrabold leading-tight"><?php echo e($eventTitle); ?></h1>
        </div>

        <div class="text-end shrink-0">
            <div class="text-white/80 text-3xl sm:text-4xl font-extrabold tabular-nums" x-text="clockTime"></div>
            <div class="text-white/80 text-xs sm:text-sm mt-1" x-text="clockDate"></div>
        </div>
    </header>

    
    <div class="flex-1 grid grid-cols-1 lg:grid-cols-[65%_35%] gap-4 sm:gap-6 min-h-0">

        
        <div class="bg-black/30 backdrop-blur rounded-3xl p-5 sm:p-7 border border-white/10 flex flex-col gap-5">
            <span class="self-start inline-flex items-center gap-2 bg-amber-400 text-emerald-950 font-bold text-sm px-4 py-1.5 rounded-full">
                <?php echo e($t('next_trip')); ?>

            </span>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($nextTrip): ?>
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class=" text-white/80 text-xl sm:text-2xl font-extrabold"><?php echo e($eventTitle); ?></h2>
                    <div class="flex items-center gap-2 text-sm sm:text-base font-semibold">
                        <span class="bg-white/15 px-3 py-1.5 rounded-lg">🕐 <?php echo e($nextTrip['time_range']); ?></span>
                        <span class="bg-white/15 px-3 py-1.5 rounded-lg">🚌 <?php echo e($nextTrip['label'][$lang]); ?></span>
                    </div>
                </div>

                <div class="flex items-center gap-2 bg-amber-400/15 border border-amber-300/40 text-amber-200 rounded-xl px-4 py-2.5 text-sm sm:text-base font-semibold">
                    🔊 <?php echo e($t('ready_alert', ['label' => $nextTrip['label'][$lang]])); ?>

                </div>

                <div class="flex-1 flex items-center justify-center gap-6 sm:gap-12 py-2">
                    <div class="flex flex-col items-center justify-center h-24 w-24 sm:h-32 sm:w-32 rounded-full bg-white/10 border border-white/25 text-center px-2">
                        <span class="text-white/80 text-2xl sm:text-4xl font-extrabold"><?php echo e($nextTrip['booked_count']); ?></span>
                        <span class="text-[10px] sm:text-xs text-white/70 mt-1 leading-tight"><?php echo e($t('bookings_count')); ?></span>
                    </div>

                    <div class="text-center">
                        <div class="text-white/80 text-5xl sm:text-8xl tabular-nums tracking-wider" x-text="countdownText">--:--</div>
                        <div class="flex justify-center gap-8 sm:gap-14 text-xs sm:text-sm text-white/70 mt-2">
                            <span><?php echo e($t('minutes_label')); ?></span>
                            <span><?php echo e($t('seconds_label')); ?></span>
                        </div>
                    </div>

                    <div class="flex flex-col items-center justify-center h-24 w-24 sm:h-32 sm:w-32 rounded-full bg-white/10 border border-white/25 text-center px-2">
                        <span class=" text-white/80 text-2xl sm:text-4xl font-extrabold"><?php echo e($nextTrip['remaining_seats']); ?></span>
                        <span class="text-[10px] sm:text-xs text-white/70 mt-1 leading-tight"><?php echo e($t('remaining_seats')); ?></span>
                    </div>
                </div>

                <div class="flex items-center gap-2 bg-amber-500/90 text-emerald-950 rounded-xl px-4 py-3 font-bold text-sm sm:text-base">
                    ⚠️ <?php echo e($t('gathering_alert', ['minutes' => $signage->gathering_alert_minutes])); ?>

                </div>
            <?php else: ?>
                <div class="flex-1 flex items-center justify-center text-white/70 text-lg sm:text-xl font-semibold text-center">
                    <?php echo e($t('no_next_trip')); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div class="bg-black/20 backdrop-blur rounded-3xl p-4 sm:p-5 border border-white/10 flex flex-col gap-3 min-h-0">
            <h3 class="text-base sm:text-lg font-extrabold px-1"><?php echo e($t('upcoming_trips')); ?></h3>

            <div class="flex-1 flex flex-col gap-3 overflow-y-auto pe-1">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $upcomingTrips; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $trip): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="rounded-2xl bg-white text-emerald-950 p-3 sm:p-4 flex items-center justify-between gap-3 shadow">
                        <div class="min-w-0">
                            <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                'inline-flex items-center gap-1 text-xs font-bold px-3 py-1 rounded-full mb-2',
                                'bg-emerald-100 text-emerald-700' => $trip['status'] === 'ready',
                                'bg-amber-100 text-amber-700' => $trip['status'] === 'soon',
                                'bg-gray-100 text-gray-500' => $trip['status'] === 'waiting',
                            ]); ?>">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($trip['status'] === 'ready'): ?> 🔔 <?php elseif($trip['status'] === 'soon'): ?> ⏳ <?php else: ?> 🕐 <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php echo e($t('status.' . $trip['status'])); ?>

                            </span>
                            <p class="font-bold truncate"><?php echo e($eventTitle); ?></p>
                            <p class="text-sm text-gray-500 truncate"><?php echo e($trip['label'][$lang]); ?></p>
                        </div>
                        <div class="text-end shrink-0">
                            <p class="font-extrabold text-sm sm:text-base"><?php echo e($trip['time_range']); ?></p>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="flex-1 flex items-center justify-center text-white/60 text-sm text-center">
                        <?php echo e($t('no_upcoming_trips')); ?>

                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>

    
    <footer class="flex flex-col gap-3">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 bg-black/30 backdrop-blur rounded-2xl p-4 border border-white/10 text-sm">
            <div class="flex items-center gap-2 min-w-0">
                <span class="text-xl shrink-0">📍</span>
                <div class="min-w-0">
                    <p class="text-white/80 font-bold"><?php echo e($t('meeting_point')); ?></p>
                    <p class="text-white/70 truncate"><?php echo e($meetingPoint ?: '—'); ?></p>
                </div>
            </div>

            <div class="flex items-center gap-2 min-w-0">
                <span class="text-xl shrink-0">⏱️</span>
                <div class="min-w-0">
                    <p class="text-white/80 font-bold"><?php echo e($t('early_arrival', ['minutes' => $signage->early_arrival_minutes])); ?></p>
                </div>
            </div>

            <div class="flex items-center gap-2 min-w-0">
                <span class="text-xl shrink-0">📞</span>
                <div class="min-w-0">
                    <p class="text-white/80 font-bold"><?php echo e($t('contact_help')); ?></p>
                    <p class="text-white/70" dir="ltr"><?php echo e($signage->contact_phone ?: '—'); ?></p>
                </div>
            </div>

            <div class="flex items-center gap-2 min-w-0">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($signage->qr_code_image_path): ?>
                    <img src="<?php echo e(asset('storage/' . $signage->qr_code_image_path)); ?>" alt="QR" class="h-12 w-12 rounded-lg bg-white p-1 shrink-0">
                <?php else: ?>
                    <span class="text-xl shrink-0">🔗</span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <p class="text-white/70 truncate"><?php echo e($t('scan_qr')); ?></p>
            </div>
        </div>

        <div class="bg-brand rounded-2xl px-6 py-3 text-center text-white/80 font-bold text-sm sm:text-base">
            <?php echo e($welcomeMessage); ?>

        </div>
    </footer>
</div>
<?php /**PATH /Users/majidalhajri/Downloads/booking/bookingrca/bookingsrca/resources/views/livewire/partials/signage-body.blade.php ENDPATH**/ ?>