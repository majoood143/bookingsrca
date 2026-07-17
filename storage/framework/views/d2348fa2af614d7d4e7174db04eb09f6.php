<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" dir="<?php echo e(app()->getLocale() === 'ar' ? 'rtl' : 'ltr'); ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e(__('event_booking.success.title')); ?> — <?php echo e(config('app.name')); ?></title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>

<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-lg">
        <!-- Success Card -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-8 py-10 text-center text-white">
                <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h1 class="text-3xl font-bold"><?php echo e(__('event_booking.success.heading')); ?></h1>
                <p class="mt-2 text-green-100"><?php echo e(__('event_booking.success.subheading')); ?></p>
            </div>

            <!-- Reference -->
            <div class="bg-gray-50 border-b border-gray-100 px-8 py-4 text-center">
                <p class="text-sm text-gray-500 uppercase tracking-wide font-semibold">
                    <?php echo e(__('event_booking.success.booking_reference')); ?></p>
                <p class="text-2xl font-bold font-mono text-gray-900 mt-1"><?php echo e($booking->booking_reference); ?></p>
            </div>

            <!-- Details -->
            <div class="px-8 py-6 space-y-4">
                <div class="flex justify-between items-start">
                    <span class="text-sm text-gray-500"><?php echo e(__('event_booking.success.event')); ?></span>
                    <span class="text-sm font-semibold text-gray-900 text-right max-w-xs">
                        <?php echo e($booking->event->getTranslation('title', app()->getLocale())); ?>

                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500"><?php echo e(__('event_booking.success.date')); ?></span>
                    <span class="text-sm font-semibold text-gray-900">
                        <?php echo e($booking->event_date->locale(app()->getLocale())->translatedFormat('l, F j, Y')); ?>

                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500"><?php echo e(__('event_booking.success.time')); ?></span>
                    <span class="text-sm font-semibold text-gray-900">
                        <?php echo e($booking->timeSlot->getTimeRange()); ?>

                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500"><?php echo e(__('event_booking.success.ticket_type')); ?></span>
                    <span class="text-sm font-semibold text-gray-900">
                        <?php echo e($booking->ticketType->getTranslation('name', app()->getLocale())); ?>

                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500"><?php echo e(__('event_booking.success.quantity')); ?></span>
                    <span class="text-sm font-semibold text-gray-900"><?php echo e($booking->quantity); ?>

                        <?php echo e(__('event_booking.success.ticket_unit')); ?></span>
                </div>
                <div class="flex justify-between items-center pt-3 border-t border-gray-100">
                    <span class="text-base font-bold text-gray-900"><?php echo e(__('event_booking.success.total_paid')); ?></span>
                    <span class="text-xl font-bold text-green-600"><?php echo $__env->make('partials.currency-amount', ['amount' => $booking->total_price], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></span>
                </div>
            </div>

            <?php $firstAttendee = $booking->attendees->first(); ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($firstAttendee && $firstAttendee->email): ?>
                <div class="px-8 pb-6">
                    <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 text-center">
                        <p class="text-sm text-blue-700">
                            <?php echo e(__('event_booking.success.confirmation_email')); ?>

                            <strong><?php echo e($firstAttendee->email); ?></strong>
                        </p>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <!-- QR Code -->
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($firstAttendee && $firstAttendee->getQrCodeUrl()): ?>
                <div class="px-8 pb-6 text-center">
                    <p class="text-sm text-gray-500 mb-3"><?php echo e(__('event_booking.success.present_qr')); ?></p>
                    <img src="<?php echo e($firstAttendee->getQrCodeUrl()); ?>" alt="QR Code"
                        class="w-40 h-40 mx-auto border border-gray-200 rounded-xl p-2">
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <!-- Print Tickets -->
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->attendees->isNotEmpty()): ?>
                <div class="px-8 pb-6 space-y-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $booking->attendees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attendee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attendee->getPdfUrl()): ?>
                            <a href="<?php echo e($attendee->getPdfUrl()); ?>" target="_blank" rel="noopener"
                                class="flex items-center justify-center gap-2 w-full px-4 py-2.5 border-2 border-gray-200 text-gray-700 font-semibold rounded-xl hover:border-gray-300 hover:bg-gray-50 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z" />
                                </svg>
                                <?php echo e(__('event_booking.success.print_ticket')); ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->attendees->count() > 1): ?>
                                    — <?php echo e($attendee->getFullName()); ?>

                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <!-- Actions -->
            <div class="px-8 pb-8 flex flex-col sm:flex-row gap-3">
                <a href="<?php echo e(route('event.booking', $booking->event->slug)); ?>"
                    class="flex-1 flex items-center justify-center gap-2 text-center px-4 py-2.5 border-2 border-gray-200 text-gray-700 font-semibold rounded-xl hover:border-gray-300 hover:bg-gray-50 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h5M20 20v-5h-5M4.5 9a8 8 0 0113.4-3.4L20 8M19.5 15a8 8 0 01-13.4 3.4L4 16" />
                    </svg>
                    <?php echo e(__('event_booking.success.book_again')); ?>

                </a>
                <a href="<?php echo e(url('/events/razat-farm-visit')); ?>"
                    class="flex-1 flex items-center justify-center gap-2 text-center px-4 py-2.5 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h3a1 1 0 001-1V10" />
                    </svg>
                    <?php echo e(__('event_booking.success.back_to_events')); ?>

                </a>

            </div>
        </div>

        <p class="text-center text-xs text-gray-400 mt-6"><?php echo e(config('app.name')); ?></p>
    </div>
</body>

</html>
<?php /**PATH /Users/majidalhajri/Downloads/booking/bookingrca/bookingsrca/resources/views/booking/success.blade.php ENDPATH**/ ?>