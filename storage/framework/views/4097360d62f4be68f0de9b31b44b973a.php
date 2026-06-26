<!DOCTYPE html>
<html lang="<?php echo e(app()->getLocale()); ?>" dir="<?php echo e(app()->getLocale() === 'ar' ? 'rtl' : 'ltr'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e(__('Booking Confirmation')); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
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
            color: black
            padding: 30px 20px;
            text-align: center;
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
        .qr-code {
            text-align: center;
            margin: 20px 0;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #eee;
            font-size: 14px;
            color: #666;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?php echo e(__('Booking Confirmed!')); ?></h1>
            <p><?php echo e(__('Reference: :reference', ['reference' => $booking->booking_reference])); ?></p>
        </div>

        <div class="content">
            <?php $primaryAttendee = $booking->attendees->first(); ?>
            <h2><?php echo e(__('Dear :name,', ['name' => $primaryAttendee ? $primaryAttendee->getFullName() : __('Valued Customer')])); ?></h2>

            <p><?php echo e(__('Your booking has been confirmed. Please find the details below:')); ?></p>

            <div class="booking-details">
                <h3><?php echo e(__('Event Details')); ?></h3>
                <div class="detail-row">
                    <strong><?php echo e(__('event.navigation.label')); ?>:</strong>
                    <span><?php echo e($booking->event->getTranslation('title', app()->getLocale())); ?></span>
                </div>
                <div class="detail-row">
                    <strong><?php echo e(__('Location')); ?>:</strong>
                    <span><?php echo e($booking->event->getTranslation('location', app()->getLocale())); ?></span>
                </div>
                <div class="detail-row">
                    <strong><?php echo e(__('Date')); ?>:</strong>
                    <span><?php echo e($booking->event_date->format('l, F j, Y')); ?></span>
                </div>
                <div class="detail-row">
                    <strong><?php echo e(__('Time')); ?>:</strong>
                    <span><?php echo e($booking->timeSlot->getTimeRange()); ?></span>
                </div>
                <div class="detail-row">
                    <strong><?php echo e(__('Ticket Type')); ?>:</strong>
                    <span><?php echo e($booking->ticketType->getTranslation('name', app()->getLocale())); ?></span>
                </div>
                <div class="detail-row">
                    <strong><?php echo e(__('Quantity')); ?>:</strong>
                    <span><?php echo e($booking->quantity); ?></span>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->extraServices->isNotEmpty()): ?>
                    <div class="detail-row">
                        <strong><?php echo e(__('Extra Services')); ?>:</strong>
                        <span>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $booking->extraServices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php echo e($service->getTranslation('name', app()->getLocale())); ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$loop->last): ?>, <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </span>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <div class="detail-row">
                    <strong><?php echo e(__('Total Amount')); ?>:</strong>
                    <span>OMR <?php echo e(number_format($booking->total_price, 3)); ?></span>
                </div>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($primaryAttendee && $primaryAttendee->getQrCodeBase64()): ?>
                <div class="qr-code">
                    <h3><?php echo e(__('Your Ticket QR Code')); ?></h3>
                    <img src="<?php echo e($primaryAttendee->getQrCodeBase64()); ?>" alt="QR Code" style="max-width: 200px;">
                    <p><?php echo e(__('Please present this QR code at the event entrance.')); ?></p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <p><?php echo e(__('If you have any questions, please contact our support team.')); ?></p>
        </div>

        <div class="footer">
            <p><?php echo e(__('Thank you for booking with us!')); ?></p>
            <p><?php echo e(config('app.name')); ?></p>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\Apache24\htdocs\bookings\resources\views/emails/booking-confirmation.blade.php ENDPATH**/ ?>