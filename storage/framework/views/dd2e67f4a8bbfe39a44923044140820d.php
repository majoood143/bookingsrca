<?php if (isset($component)) { $__componentOriginal166a02a7c5ef5a9331faf66fa665c256 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-panels::components.page.index','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-panels::page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php
        $locale = app()->getLocale();
        $statusColors = [
            'pending' => '#d97706',
            'confirmed' => '#16a34a',
            'cancelled' => '#dc2626',
            'checked_in' => '#4f46e5',
        ];
    ?>

    <div
        x-data="{
            fullscreen: false,
            toggleFullscreen() {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen?.();
                    this.fullscreen = true;
                } else {
                    document.exitFullscreen?.();
                    this.fullscreen = false;
                }
            }
        }"
        x-on:fullscreenchange.window="fullscreen = !!document.fullscreenElement"
        class="space-y-4"
    >
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-lg font-bold"><?php echo e(__('attendee_check_in.title')); ?></h2>
            <button
                type="button"
                x-on:click="toggleFullscreen()"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-md text-sm font-medium text-white shrink-0 transition-colors duration-150"
                style="background-color: #4f46e5; min-height: 44px;"
                onmouseover="this.style.backgroundColor='#4338ca'"
                onmouseout="this.style.backgroundColor='#4f46e5'"
            >
                <span x-show="!fullscreen"><?php echo e(__('attendee_check_in.actions.fullscreen_on')); ?></span>
                <span x-show="fullscreen" x-cloak><?php echo e(__('attendee_check_in.actions.fullscreen_off')); ?></span>
            </button>
        </div>

        <form wire:submit.prevent="search" x-on:keydown.enter.prevent="$wire.search()">
            <?php echo e($this->form); ?>

        </form>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($candidateBookings && $candidateBookings->count() > 1): ?>
            <div class="space-y-2">
                <p class="text-sm text-gray-600"><?php echo e(__('attendee_check_in.disambiguation.prompt')); ?></p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $candidateBookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $candidate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button
                        type="button"
                        wire:click="selectCandidateBooking(<?php echo e($candidate->id); ?>)"
                        class="w-full text-left border rounded-xl p-4 bg-white shadow-sm"
                        style="min-height: 56px;"
                    >
                        <span class="font-mono font-semibold"><?php echo e($candidate->booking_reference); ?></span>
                        — <?php echo e($candidate->event?->getTranslation('title', $locale)); ?>

                        (<?php echo e($candidate->event_date?->format('Y-m-d')); ?>)
                    </button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking): ?>
            <?php $remaining = $booking->attendees->where('checked_in', false)->count(); ?>

            <div class="border rounded-xl p-4 bg-white shadow-sm space-y-1">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="font-mono text-lg font-bold"><?php echo e($booking->booking_reference); ?></span>
                    <span
                        class="inline-flex px-2 py-1 rounded-full text-xs font-medium text-white"
                        style="background-color: <?php echo e($statusColors[$booking->status] ?? '#6b7280'); ?>;"
                    >
                        <?php echo e(__('booking.tabs.' . $booking->status)); ?>

                    </span>
                </div>
                <p class="text-sm text-gray-700"><?php echo e($booking->event?->getTranslation('title', $locale)); ?></p>
                <p class="text-sm text-gray-600">
                    <?php echo e($booking->event_date?->format('Y-m-d')); ?> · <?php echo e($booking->timeSlot?->getTimeRange()); ?>

                </p>
                <p class="text-sm text-gray-600"><?php echo e($booking->ticketType?->getTranslation('name', $locale)); ?></p>
                <p class="text-sm text-gray-600">
                    <?php echo e(__('attendee_check_in.summary', ['checked_in' => $booking->attendees->count() - $remaining, 'total' => $booking->attendees->count()])); ?>

                </p>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($remaining > 0): ?>
                <button
                    type="button"
                    wire:click="checkInAll"
                    wire:confirm="<?php echo e(__('attendee_check_in.actions.check_in_all_confirm', ['count' => $remaining])); ?>"
                    wire:loading.attr="disabled"
                    wire:target="checkInAll"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-md text-white font-semibold transition-colors duration-150 disabled:opacity-50"
                    style="background-color: #16a34a; min-height: 48px;"
                    onmouseover="this.style.backgroundColor='#15803d'"
                    onmouseout="this.style.backgroundColor='#16a34a'"
                >
                    <?php echo e(__('attendee_check_in.actions.check_in_all', ['count' => $remaining])); ?>

                </button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="space-y-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $booking->attendees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attendee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-center justify-between gap-3 border rounded-xl p-4 bg-white shadow-sm">
                        <div class="min-w-0">
                            <p class="font-semibold truncate"><?php echo e($attendee->getFullName()); ?></p>
                            <p class="text-xs text-gray-500 font-mono"><?php echo e($attendee->ticket_number); ?></p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attendee->ticketType): ?>
                                <p class="text-xs text-gray-500"><?php echo e($attendee->ticketType->getTranslation('name', $locale)); ?></p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <button
                            type="button"
                            wire:click="toggleAttendee(<?php echo e($attendee->id); ?>)"
                            wire:loading.attr="disabled"
                            wire:target="toggleAttendee(<?php echo e($attendee->id); ?>)"
                            role="switch"
                            aria-checked="<?php echo e($attendee->checked_in ? 'true' : 'false'); ?>"
                            class="relative inline-flex shrink-0 items-center rounded-full transition-colors duration-150"
                            style="width: 64px; height: 36px; background-color: <?php echo e($attendee->checked_in ? '#16a34a' : '#d1d5db'); ?>;"
                        >
                            <span
                                class="inline-block rounded-full bg-white shadow transform transition-transform duration-150"
                                style="width: 28px; height: 28px; margin: 4px; transform: translateX(<?php echo e($attendee->checked_in ? '28px' : '0'); ?>);"
                            ></span>
                        </button>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <button
                type="button"
                wire:click="resetSearch"
                class="w-full inline-flex items-center justify-center px-4 py-2 rounded-md text-sm font-medium border border-gray-300"
                style="min-height: 44px;"
            >
                <?php echo e(__('attendee_check_in.actions.scan_another')); ?>

            </button>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $attributes = $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $component = $__componentOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?>
<?php /**PATH /Users/majidalhajri/Downloads/booking/bookingrca/bookingsrca/resources/views/filament/pages/attendee-check-in.blade.php ENDPATH**/ ?>