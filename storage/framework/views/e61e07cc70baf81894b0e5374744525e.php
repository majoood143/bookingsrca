<?php
    $attendees = $timeSlot->bookings->flatMap(
        fn($booking) => $booking->attendees->map(fn($attendee) => tap($attendee, fn($a) => $a->setRelation('booking', $booking)))
    );
    $totalAttendees = $attendees->count();
    $checkedInCount = $attendees->where('checked_in', true)->count();
    $emailedCount = $attendees->where('email_sent', true)->count();
?>

<div class="space-y-4">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($totalAttendees > 0): ?>
        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 px-1 text-sm text-gray-600">
            <?php echo e(__('time_slot.attendees_modal.summary', [
                'checked_in' => $checkedInCount,
                'emailed' => $emailedCount,
                'total' => $totalAttendees,
            ])); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $attendees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attendee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="border rounded-xl p-4 bg-white shadow-sm">
            <div class="flex-1">
                <h3 class="text-lg font-bold"><?php echo e($attendee->getFullName()); ?></h3>
                <p class="text-sm text-gray-600"><?php echo e($attendee->email ?: __('booking.attendees_modal.no_email')); ?></p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attendee->phone): ?>
                    <p class="text-sm text-gray-600"><?php echo e($attendee->phone); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <p class="text-sm text-gray-600"><?php echo e(__('booking_attendee.fields.ticket_number')); ?>: <span class="font-mono"><?php echo e($attendee->ticket_number); ?></span></p>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attendee->ticketType): ?>
                    <p class="text-sm text-gray-700 mt-1">
                        <span class="font-semibold"><?php echo e(__('booking_attendee.fields.ticket_type')); ?>:</span>
                        <?php echo e($attendee->ticketType->getTranslation('name', app()->getLocale())); ?>

                        <span class="text-green-600 font-semibold"><?php echo $__env->make('partials.currency-amount', ['amount' => $attendee->ticket_price], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></span>
                    </p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="mt-2 flex flex-wrap gap-2">
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                        <?php echo e(__('time_slot.attendees_modal.booking_ref')); ?>: <?php echo e($attendee->booking->booking_reference); ?>

                    </span>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attendee->email_sent): ?>
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            ✓ <?php echo e(__('booking.attendees_modal.email_sent')); ?>

                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            ✗ <?php echo e(__('booking.attendees_modal.email_not_sent')); ?>

                        </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attendee->checked_in): ?>
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            ✓ <?php echo e(__('booking.attendees_modal.checked_in')); ?>

                        </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="text-center text-sm text-gray-500 py-8">
            <?php echo e(__('time_slot.attendees_modal.empty')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /Users/majidalhajri/Downloads/booking/bookingrca/bookingsrca/resources/views/filament/modals/time-slot-attendees.blade.php ENDPATH**/ ?>