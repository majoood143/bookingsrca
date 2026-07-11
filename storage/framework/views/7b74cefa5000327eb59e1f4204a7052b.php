<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', $locale)); ?>" dir="<?php echo e($isRtl ? 'rtl' : 'ltr'); ?>">
<head>
    <meta charset="UTF-8">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', 'Tahoma', 'Segoe UI', sans-serif;
            font-size: 22px;
            color: #000;
            background: #fff;
            width: <?php echo e($paperWidth); ?>px;
            padding: 20px 24px;
            direction: <?php echo e($isRtl ? 'rtl' : 'ltr'); ?>;
        }

        .center {
            text-align: center;
        }

        .logo {
            height: 70px;
            width: auto;
            margin-bottom: 8px;
        }

        .badge {
            display: inline-block;
            border: 2px solid #000;
            padding: 6px 18px;
            border-radius: 30px;
            font-size: 22px;
            font-weight: bold;
            letter-spacing: 1px;
            margin-top: 6px;
        }

        .event-name {
            text-align: center;
            font-size: 30px;
            font-weight: bold;
            margin-top: 14px;
        }

        .divider {
            border-top: 3px dashed #000;
            margin: 18px 0;
        }

        .section-label {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 2px solid #000;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 5px 0;
            font-size: 22px;
        }

        .info-key {
            font-weight: 600;
        }

        .info-val {
            text-align: <?php echo e($isRtl ? 'left' : 'right'); ?>;
            word-break: break-word;
        }

        .barcode-strip {
            text-align: center;
            padding-top: 6px;
        }

        .barcode-strip img {
            max-width: 100%;
            height: 90px;
        }

        .barcode-ref {
            font-family: 'Courier New', monospace;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 2px;
            margin-top: 8px;
        }

        .barcode-note {
            font-size: 16px;
            margin-top: 6px;
        }

        .footer {
            text-align: center;
            font-size: 16px;
            margin-top: 18px;
        }
    </style>
</head>
<body>
    <div class="center">
        <img class="logo" src="<?php echo e(asset('storage/images/horizontalLogo-02.svg')); ?>" alt="<?php echo e(config('app.name')); ?>">
        <div><span class="badge" dir="ltr"><?php echo e($attendee->ticket_number); ?></span></div>
    </div>

    <div class="event-name"><?php echo e($booking->event->getTranslation('title', $locale)); ?></div>

    <div class="divider"></div>

    <div class="section-label"><?php echo e($t('attendee')); ?></div>
    <div class="info-row">
        <span class="info-key"><?php echo e($t('name')); ?></span>
        <span class="info-val"><?php echo e($attendee->getFullName()); ?></span>
    </div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attendee->email): ?>
        <div class="info-row">
            <span class="info-key"><?php echo e($t('email')); ?></span>
            <span class="info-val"><?php echo e($attendee->email); ?></span>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attendee->phone): ?>
        <div class="info-row">
            <span class="info-key"><?php echo e($t('phone')); ?></span>
            <span class="info-val" dir="ltr"><?php echo e($attendee->phone); ?></span>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <div class="info-row">
        <span class="info-key"><?php echo e($t('ticket_type')); ?></span>
        <span class="info-val"><?php echo e($attendee->ticketType?->getTranslation('name', $locale)); ?></span>
    </div>

    <div class="divider"></div>

    <div class="section-label"><?php echo e($t('event_details')); ?></div>
    <div class="info-row">
        <span class="info-key"><?php echo e($t('date')); ?></span>
        <span class="info-val"><?php echo e($dateFormatted); ?></span>
    </div>
    <div class="info-row">
        <span class="info-key"><?php echo e($t('time')); ?></span>
        <span class="info-val"><?php echo e($booking->timeSlot->getTimeRange()); ?></span>
    </div>
    <div class="info-row">
        <span class="info-key"><?php echo e($t('location')); ?></span>
        <span class="info-val"><?php echo e($booking->event->getTranslation('location', $locale)); ?></span>
    </div>

    <div class="divider"></div>

    <div class="barcode-strip">
        <img src="<?php echo e($barcode); ?>" alt="<?php echo e($t('booking_reference')); ?>">
        <div class="barcode-ref" dir="ltr"><?php echo e($booking->booking_reference); ?></div>
        <div class="barcode-note"><?php echo e($t('present_barcode')); ?></div>
    </div>

    <div class="footer">
        <div><?php echo e(config('app.name')); ?></div>
        <div>&copy; <?php echo e(date('Y')); ?></div>
    </div>
</body>
</html>
<?php /**PATH /Users/majidalhajri/Downloads/booking/bookingrca/bookingsrca/resources/views/bookings/attendee-ticket-thermal.blade.php ENDPATH**/ ?>