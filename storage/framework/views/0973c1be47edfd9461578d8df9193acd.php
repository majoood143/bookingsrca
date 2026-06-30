<?php
    $isAr  = ($locale ?? 'en') === 'ar';
    $dir   = $isAr ? 'rtl' : 'ltr';
    $lang  = $isAr ? 'ar' : 'en';
    $t     = fn(string $key) => trans("event_booking.ticket.$key", [], $lang);
    $dateFormatted = $isAr
        ? $booking->event_date->locale('ar')->translatedFormat('l، j F Y')
        : $booking->event_date->format('l, F j, Y');
?>
<!DOCTYPE html>
<html lang="<?php echo e($lang); ?>" dir="<?php echo e($dir); ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo e($t('event_ticket')); ?> - <?php echo e($attendee->ticket_number); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Almarai', 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            direction: <?php echo e($dir); ?>;
        }

        .header {
            height: 83mm;
            background-size: 100% 100%;
            background-repeat: no-repeat;
            background-position: center top;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .header-table {
            width: 100%;
            height: 83mm;
        }

        .header-text {
            <?php if($isAr): ?>
            padding: 15mm 28px 23mm 10px;
            text-align: right;
            <?php else: ?>
            padding: 15mm 10px 23mm 28px;
            text-align: left;
            <?php endif; ?>
            vertical-align: middle;
        }

        .header-kicker {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #6b7280;
            margin-bottom: 5px;
        }

        .header .event-name {
            font-size: 19px;
            font-weight: bold;
            color: #14532d;
            margin-bottom: 10px;
        }

        .ticket-number {
            background: #ecfdf5;
            border: 1px solid #14532d;
            color: #14532d;
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .qr-cell {
            width: 110px;
            text-align: center;
            vertical-align: middle;
            <?php if($isAr): ?>
            padding: 15mm 10px 23mm 28px;
            <?php else: ?>
            padding: 15mm 28px 23mm 10px;
            <?php endif; ?>
        }

        .qr-cell img {
            width: 76px;
            height: 76px;
            background: #fff;
            padding: 4px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
        }

        .qr-caption {
            font-size: 8px;
            margin-top: 4px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .section {
            margin-bottom: 22px;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 15px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 12px;
            padding-bottom: 6px;
            border-bottom: 2px solid #667eea;
            text-align: <?php echo e($isAr ? 'right' : 'left'); ?>;
        }

        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 12px;
        }

        .info-row {
            display: table-row;
        }

        .info-label {
            display: table-cell;
            font-weight: bold;
            <?php if($isAr): ?>
            padding: 6px 0 6px 15px;
            text-align: right;
            <?php else: ?>
            padding: 6px 15px 6px 0;
            text-align: left;
            <?php endif; ?>
            width: 35%;
            color: #666;
        }

        .info-value {
            display: table-cell;
            padding: 6px 0;
            color: #333;
            text-align: <?php echo e($isAr ? 'right' : 'left'); ?>;
        }

        .services-list {
            list-style: none;
            padding: 0;
        }

        .services-list li {
            padding: 8px 0;
            border-bottom: 1px dashed #e5e7eb;
            text-align: <?php echo e($isAr ? 'right' : 'left'); ?>;
        }

        .services-list li:last-child {
            border-bottom: none;
        }

        .service-amount {
            color: #6b7280;
            font-size: 11px;
        }

        .entry-pass {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 20px;
            border-radius: 8px;
            margin-top: 15px;
            text-align: center;
        }

        .entry-pass img {
            width: 150px;
            height: 150px;
            background: #fff;
            padding: 8px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }

        .entry-pass p {
            font-size: 11px;
            color: #6b7280;
            margin-top: 10px;
        }

        .entry-pass .ticket-number-mono {
            font-family: monospace;
            font-size: 13px;
            color: #333;
            margin-top: 6px;
            letter-spacing: 0.5px;
        }

        .divider {
            border-top: 2px dashed #d1d5db;
            margin: 20px 0;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px dashed #d1d5db;
            text-align: center;
            color: #6b7280;
            font-size: 10px;
        }

        .footer p {
            margin: 2px 0;
        }
    </style>
</head>
<body>
    <div class="header" style="background-image: url('<?php echo e($headerBg); ?>');">
        <table class="header-table">
            <tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isAr): ?>
                <td class="qr-cell">
                    <img src="<?php echo e($qrCode); ?>" alt="QR code">
                    <div class="qr-caption"><?php echo e($t('scan_to_verify')); ?></div>
                </td>
                <td class="header-text">
                    <div class="header-kicker"><?php echo e($t('event_ticket')); ?></div>
                    <div class="event-name"><?php echo e($booking->event->getTranslation('title', 'ar')); ?></div>
                    <div class="ticket-number"><span dir="ltr"><?php echo e($attendee->ticket_number); ?></span></div>
                </td>
                <?php else: ?>
                <td class="header-text">
                    <div class="header-kicker"><?php echo e($t('event_ticket')); ?></div>
                    <div class="event-name"><?php echo e($booking->event->getTranslation('title', 'en')); ?></div>
                    <div class="ticket-number"><?php echo e($attendee->ticket_number); ?></div>
                </td>
                <td class="qr-cell">
                    <img src="<?php echo e($qrCode); ?>" alt="QR code">
                    <div class="qr-caption"><?php echo e($t('scan_to_verify')); ?></div>
                </td>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tr>
        </table>
    </div>

    <!-- Attendee -->
    <div class="section">
        <div class="section-title"><?php echo e($t('attendee')); ?></div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label"><?php echo e($t('name')); ?>:</div>
                <div class="info-value"><?php echo e($attendee->getFullName()); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label"><?php echo e($t('email')); ?>:</div>
                <div class="info-value"><?php echo e($attendee->email); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label"><?php echo e($t('ticket_type')); ?>:</div>
                <div class="info-value"><?php echo e($booking->ticketType->getTranslation('name', $lang)); ?></div>
            </div>
        </div>
    </div>

    <div class="divider"></div>

    <!-- Event Details -->
    <div class="section">
        <div class="section-title"><?php echo e($t('event_details')); ?></div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label"><?php echo e($t('date')); ?>:</div>
                <div class="info-value"><?php echo e($dateFormatted); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label"><?php echo e($t('time')); ?>:</div>
                <div class="info-value"><?php echo e($booking->timeSlot->getTimeRange()); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label"><?php echo e($t('location')); ?>:</div>
                <div class="info-value"><?php echo e($booking->event->getTranslation('location', $lang)); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label"><?php echo e($t('organizer')); ?>:</div>
                <div class="info-value"><?php echo e($booking->event->organizer); ?></div>
            </div>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->extraServices->count() > 0): ?>
    <div class="divider"></div>

    <!-- Extra Services -->
    <div class="section">
        <div class="section-title"><?php echo e($t('extra_services')); ?></div>
        <ul class="services-list">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $booking->extraServices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li>
                <strong><?php echo e($service->getTranslation('name', $lang)); ?></strong><br>
                <span class="service-amount"><?php echo e($t('quantity')); ?>: <?php echo e($service->pivot->quantity); ?></span>
            </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </ul>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="divider"></div>

    <!-- Entry Pass -->
    <div class="section">
        <div class="section-title"><?php echo e($t('entry_pass')); ?></div>
        <div class="entry-pass">
            <img src="<?php echo e($qrCode); ?>" alt="QR code">
            <div class="ticket-number-mono"><span dir="ltr"><?php echo e($attendee->ticket_number); ?></span></div>
            <p><?php echo e($t('present_qr')); ?></p>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p><strong><?php echo e($t('booking_reference')); ?>:</strong> <span dir="ltr"><?php echo e($booking->booking_reference); ?></span></p>
        <p><strong><?php echo e($t('booked_on')); ?>:</strong> <span dir="ltr"><?php echo e($booking->created_at->format('M d, Y H:i')); ?></span></p>
        <p style="margin-top: 12px;"><?php echo e($t('support_note')); ?></p>
        <p>&copy; <?php echo e(date('Y')); ?> <?php echo e(config('app.name')); ?>. <?php echo e($t('all_rights')); ?></p>
    </div>
</body>
</html>
<?php /**PATH C:\Apache24\htdocs\bookings\resources\views/tickets/individual.blade.php ENDPATH**/ ?>