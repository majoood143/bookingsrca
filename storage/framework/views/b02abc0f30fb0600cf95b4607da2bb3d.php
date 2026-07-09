<!DOCTYPE html>
<html lang="<?php echo e($locale); ?>" dir="<?php echo e($locale === 'ar' ? 'rtl' : 'ltr'); ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e(__('event_booking.email.ticket_heading')); ?></title>
    <style>
        body {
            font-family: <?php echo e($locale === 'ar' ? "'Segoe UI', Tahoma, Arial" : 'Arial'); ?>, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            direction: <?php echo e($locale === 'ar' ? 'rtl' : 'ltr'); ?>;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }

        .content {
            background: white;
            padding: 30px;
            border: 1px solid #ddd;
            border-top: none;
        }

        .ticket-info {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .ticket-info table {
            width: 100%;
            border-collapse: collapse;
        }

        .ticket-info td {
            padding: 8px 0;
            vertical-align: top;
        }

        .ticket-info td.label {
            font-weight: bold;
            width: 40%;
            color: #555;
            <?php echo e($locale === 'ar' ? 'padding-left: 12px;' : 'padding-right: 12px;'); ?>

        }

        .qr-section {
            text-align: center;
            margin: 30px 0;
        }

        .qr-section img {
            max-width: 140px;
        }

        .attachments-list {
            <?php echo e($locale === 'ar' ? 'padding-right: 20px; padding-left: 0;' : 'padding-left: 20px; padding-right: 0;'); ?>

        }

        .footer {
            background: #f5f5f5;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-radius: 0 0 10px 10px;
        }

        .footer p {
            margin: 4px 0;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1><?php echo e(__('event_booking.email.ticket_heading')); ?></h1>
        <p><?php echo e($booking->event->getTranslation('title', $locale)); ?></p>
    </div>

    <div class="content">
        <h2><?php echo e(__('event_booking.email.ticket_hello', ['name' => $attendee->first_name])); ?></h2>

        <p><?php echo e(__('event_booking.email.ticket_intro')); ?></p>

        <div class="ticket-info">
            <table>
                <tr>
                    <td class="label"><?php echo e(__('event_booking.email.ticket_number')); ?>:</td>
                    <td><strong><?php echo e($attendee->ticket_number); ?></strong></td>
                </tr>
                <tr>
                    <td class="label"><?php echo e(__('event_booking.email.ticket_attendee')); ?>:</td>
                    <td><?php echo e($attendee->getFullName()); ?></td>
                </tr>
                <tr>
                    <td class="label"><?php echo e(__('event_booking.email.event')); ?>:</td>
                    <td><?php echo e($booking->event->getTranslation('title', $locale)); ?></td>
                </tr>
                <tr>
                    <td class="label"><?php echo e(__('event_booking.email.date')); ?>:</td>
                    <td><?php echo e($booking->event_date->format('l, F j, Y')); ?></td>
                </tr>
                <tr>
                    <td class="label"><?php echo e(__('event_booking.email.time')); ?>:</td>
                    <td><?php echo e($booking->timeSlot->getTimeRange()); ?></td>
                </tr>
                <tr>
                    <td class="label"><?php echo e(__('event_booking.email.location')); ?>:</td>
                    <td><?php echo e($booking->event->getTranslation('location', $locale)); ?></td>
                </tr>
                <tr>
                    <td class="label"><?php echo e(__('event_booking.email.ticket_type')); ?>:</td>
                    <td><?php echo e($booking->ticketType->getTranslation('name', $locale)); ?></td>
                </tr>
            </table>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->extraServices->count() > 0): ?>
            <h3><?php echo e(__('event_booking.email.ticket_extra_services')); ?>:</h3>
            <ul class="attachments-list">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $booking->extraServices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($service->getTranslation('name', $locale)); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </ul>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="qr-section">
            <h3><?php echo e(__('event_booking.email.ticket_qr_heading')); ?></h3>
            <img src="<?php echo e($attendee->getQrCodeBase64()); ?>" alt="QR Code">
            <p><strong><?php echo e(__('event_booking.email.ticket_qr_important')); ?>:</strong>
                <?php echo e(__('event_booking.email.ticket_qr_notice')); ?></p>
        </div>

        <p><strong><?php echo e(__('event_booking.email.ticket_attachments')); ?>:</strong></p>
        <ul class="attachments-list">
            <li><?php echo e(__('event_booking.email.ticket_pdf_label', ['number' => $attendee->ticket_number])); ?></li>
            <li><?php echo e(__('event_booking.email.ticket_qr_label', ['number' => $attendee->ticket_number])); ?></li>
        </ul>

        <p><?php echo e(__('event_booking.email.ticket_see_you')); ?></p>

        <p style="margin-top: 30px;">
            <small><strong><?php echo e(__('event_booking.email.ticket_reference')); ?>:</strong>
                <?php echo e($booking->booking_reference); ?></small>
        </p>
    </div>

    <div class="footer">
        <p><?php echo e(__('event_booking.email.ticket_automated')); ?></p>
        <p><?php echo e(__('event_booking.email.ticket_support')); ?></p>
        <p>&copy; <?php echo e(date('Y')); ?> <?php echo e(config('app.name')); ?>. <?php echo e(__('event_booking.email.ticket_all_rights')); ?></p>
    </div>
</body>

</html>
<?php /**PATH /Users/majidalhajri/Downloads/booking/bookingrca/bookingsrca/resources/views/emails/individual-ticket.blade.php ENDPATH**/ ?>