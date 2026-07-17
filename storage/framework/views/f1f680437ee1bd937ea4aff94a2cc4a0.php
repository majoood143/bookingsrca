<!DOCTYPE html>
<html lang="<?php echo e($locale); ?>" dir="<?php echo e($locale === 'ar' ? 'rtl' : 'ltr'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e(__('event_booking.email.subject', ['reference' => $booking->booking_reference])); ?></title>
    <style>
        body {
            font-family: <?php echo e($locale === 'ar' ? "'Segoe UI', Tahoma, Arial" : "Arial"); ?>, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
            direction: <?php echo e($locale === 'ar' ? 'rtl' : 'ltr'); ?>;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0 0 8px;
            font-size: 24px;
        }
        .header p {
            margin: 0;
            opacity: 0.9;
        }
        .content {
            padding: 30px 20px;
        }
        .booking-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .booking-details h3 {
            margin: 0 0 16px;
            font-size: 16px;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .detail-label {
            color: #666;
            <?php echo e($locale === 'ar' ? 'margin-left: 16px;' : 'margin-right: 16px;'); ?>

        }
        .detail-value {
            font-weight: 600;
            text-align: <?php echo e($locale === 'ar' ? 'left' : 'right'); ?>;
        }
        .qr-code {
            text-align: center;
            margin: 24px 0;
        }
        .qr-code h3 {
            margin: 0 0 12px;
            color: #444;
        }
        .qr-code p {
            margin: 10px 0 0;
            color: #666;
            font-size: 14px;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #eee;
            font-size: 14px;
            color: #666;
        }
        .footer p {
            margin: 4px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?php echo e(__('event_booking.email.confirmed')); ?></h1>
            <p><?php echo e(__('event_booking.email.reference', ['reference' => $booking->booking_reference])); ?></p>
        </div>

        <div class="content">
            <?php $primaryAttendee = $booking->attendees->first(); ?>
            <h2><?php echo e(__('event_booking.email.dear', ['name' => $primaryAttendee ? $primaryAttendee->getFullName() : __('event_booking.email.valued_customer')])); ?></h2>

            <p><?php echo e(__('event_booking.email.details_below')); ?></p>

            <div class="booking-details">
                <h3><?php echo e(__('event_booking.email.event_details')); ?></h3>

                <div class="detail-row">
                    <span class="detail-label"><?php echo e(__('event_booking.email.event')); ?></span>
                    <span class="detail-value"><?php echo e($booking->event->getTranslation('title', $locale)); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><?php echo e(__('event_booking.email.location')); ?></span>
                    <span class="detail-value"><?php echo e($booking->event->getTranslation('location', $locale)); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><?php echo e(__('event_booking.email.date')); ?></span>
                    <span class="detail-value"><?php echo e($booking->event_date->format('l, F j, Y')); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><?php echo e(__('event_booking.email.time')); ?></span>
                    <span class="detail-value"><?php echo e($booking->timeSlot->getTimeRange()); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><?php echo e(__('event_booking.email.ticket_type')); ?></span>
                    <span class="detail-value"><?php echo e($booking->ticketType->getTranslation('name', $locale)); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><?php echo e(__('event_booking.email.quantity')); ?></span>
                    <span class="detail-value"><?php echo e($booking->quantity); ?></span>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->extraServices->isNotEmpty()): ?>
                    <div class="detail-row">
                        <span class="detail-label"><?php echo e(__('event_booking.email.extra_services')); ?></span>
                        <span class="detail-value">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $booking->extraServices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php echo e($service->getTranslation('name', $locale)); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$loop->last): ?>, <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </span>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="detail-row">
                    <span class="detail-label"><?php echo e(__('event_booking.email.total_amount')); ?></span>
                    <span class="detail-value"><?php echo $__env->make('partials.currency-amount', ['amount' => $booking->total_price], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></span>
                </div>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($primaryAttendee && $primaryAttendee->getQrCodeBase64()): ?>
                <div class="qr-code">
                    <h3><?php echo e(__('event_booking.email.qr_heading')); ?></h3>
                    <img src="<?php echo e($primaryAttendee->getQrCodeBase64()); ?>" alt="QR Code" style="max-width: 200px;">
                    <p><?php echo e(__('event_booking.email.qr_notice')); ?></p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <p><?php echo e(__('event_booking.email.support')); ?></p>
        </div>

        <div class="footer">
            <p><?php echo e(__('event_booking.email.thank_you')); ?></p>
            <p><?php echo e(config('app.name')); ?></p>
        </div>
    </div>
</body>
</html>
<?php /**PATH /Users/majidalhajri/Downloads/booking/bookingrca/bookingsrca/resources/views/emails/booking-confirmation.blade.php ENDPATH**/ ?>