<?php
    $locale = app()->getLocale();
    $isRtl = $locale === 'ar';
?>
<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', $locale)); ?>" dir="<?php echo e($isRtl ? 'rtl' : 'ltr'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - <?php echo e($booking->booking_reference); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', 'Courier New', monospace;
            font-size: 13px;
            color: #111;
            width: 320px;
            margin: 0 auto;
            padding: 16px;
        }

        .center {
            text-align: center;
        }

        .title {
            font-size: 16px;
            font-weight: bold;
        }

        .muted {
            color: #555;
            font-size: 11px;
        }

        .divider {
            border-top: 1px dashed #999;
            margin: 10px 0;
        }

        .row {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            padding: 2px 0;
        }

        .row .label {
            color: #333;
        }

        .row .value {
            font-weight: bold;
            text-align: right;
        }

        .attendee {
            padding: 4px 0;
            border-bottom: 1px dotted #ccc;
        }

        .attendee:last-child {
            border-bottom: none;
        }

        .total-row {
            font-size: 16px;
            font-weight: bold;
            border-top: 1px solid #111;
            padding-top: 6px;
            margin-top: 6px;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 8px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            background: #e5e7eb;
        }

        .footer {
            margin-top: 16px;
            text-align: center;
            font-size: 11px;
            color: #555;
        }

        .no-print {
            text-align: center;
            margin-top: 16px;
        }

        <?php if($isRtl): ?>
            body {
                font-family: 'DejaVu Sans', 'Tahoma', sans-serif;
            }

            .row .value {
                text-align: left;
            }
        <?php endif; ?>

        @media print {
            .no-print {
                display: none;
            }

            body {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="center">
        <div class="title"><?php echo e(config('app.name')); ?></div>
        <div class="muted"><?php echo e($booking->event->getTranslation('location', $locale) ?: ''); ?></div>
    </div>

    <div class="divider"></div>

    <div class="row"><span class="label"><?php echo e(__('booking.receipt.receipt_number')); ?></span><span class="value"><?php echo e($booking->booking_reference); ?></span></div>
    <div class="row"><span class="label"><?php echo e(__('booking.receipt.date')); ?></span><span class="value"><?php echo e($booking->created_at->format('Y-m-d H:i')); ?></span></div>
    <div class="row"><span class="label"><?php echo e(__('booking.receipt.status')); ?></span><span class="value"><?php echo e(__('booking.options.status.' . $booking->status)); ?></span></div>

    <div class="divider"></div>

    <div class="row"><span class="label"><?php echo e(__('booking.receipt.event')); ?></span><span class="value"><?php echo e($booking->event->getTranslation('title', $locale)); ?></span></div>
    <div class="row"><span class="label"><?php echo e(__('booking.receipt.date')); ?></span><span class="value"><?php echo e($booking->event_date->format('Y-m-d')); ?></span></div>
    <div class="row"><span class="label"><?php echo e(__('booking.receipt.time')); ?></span><span class="value"><?php echo e($booking->timeSlot->getTimeRange()); ?></span></div>
    <div class="row"><span class="label"><?php echo e(__('booking.receipt.qty')); ?></span><span class="value"><?php echo e($booking->quantity); ?></span></div>

    <div class="divider"></div>

    <div class="muted"><?php echo e(__('booking.receipt.attendees')); ?></div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $booking->attendees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $attendee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="attendee">
            <?php echo e($index + 1); ?>. <?php echo e($attendee->getFullName()); ?>

            <div class="muted"><?php echo e($attendee->ticket_number); ?></div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->extraServices->count() > 0): ?>
        <div class="divider"></div>
        <div class="muted"><?php echo e(__('booking.receipt.extra_services')); ?></div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $booking->extraServices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="row">
                <span class="label"><?php echo e($service->getTranslation('name', $locale)); ?> &times; <?php echo e($service->pivot->quantity); ?></span>
                <span class="value"><?php echo $__env->make('partials.currency-amount', ['amount' => $service->pivot->quantity * $service->pivot->price], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></span>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="divider"></div>

    <div class="row"><span class="label"><?php echo e(__('booking.receipt.tickets')); ?></span><span class="value"><?php echo $__env->make('partials.currency-amount', ['amount' => $booking->ticket_price], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></span></div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->services_price > 0): ?>
        <div class="row"><span class="label"><?php echo e(__('booking.receipt.services')); ?></span><span class="value"><?php echo $__env->make('partials.currency-amount', ['amount' => $booking->services_price], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></span></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <div class="row total-row"><span class="label"><?php echo e(__('booking.receipt.total')); ?></span><span class="value"><?php echo $__env->make('partials.currency-amount', ['amount' => $booking->total_price], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></span></div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->payments->count() > 0): ?>
        <div class="divider"></div>
        <div class="muted"><?php echo e(__('booking.receipt.payments')); ?></div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $booking->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="row">
                <span class="label"><?php echo e(__('booking.payments.methods.' . $payment->payment_method)); ?></span>
                <span class="value"><?php echo $__env->make('partials.currency-amount', ['amount' => $payment->amount], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></span>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <div class="row"><span class="label"><?php echo e(__('booking.receipt.paid')); ?></span><span class="value"><?php echo $__env->make('partials.currency-amount', ['amount' => $booking->total_paid], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></span></div>
        <div class="row"><span class="label"><?php echo e(__('booking.receipt.balance_due')); ?></span><span class="value"><?php echo $__env->make('partials.currency-amount', ['amount' => $booking->balance_due], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></span></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="footer">
        <p><?php echo e(__('booking.receipt.thank_you')); ?></p>
        <p><?php echo e(__('booking.receipt.present_note')); ?></p>
        <p><?php echo e(now()->format('Y-m-d H:i')); ?></p>
    </div>

    <div class="no-print">
        <button onclick="window.print()" style="padding:10px 20px;font-size:14px;">Print</button>
    </div>

    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</body>
</html>
<?php /**PATH /Users/majidalhajri/Downloads/booking/bookingrca/bookingsrca/resources/views/bookings/pos-receipt.blade.php ENDPATH**/ ?>