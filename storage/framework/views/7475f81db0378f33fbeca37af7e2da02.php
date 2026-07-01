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
        @page {
            size: A4 portrait;
            margin: 14mm 12mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Almarai', 'DejaVu Sans', sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #1f2937;
            direction: <?php echo e($dir); ?>;
            background: #fff;
        }

        /* ── TICKET WRAPPER ── */
        .ticket {
            border: 1px solid #d1d5db;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0,0,0,.08);
        }

        /* ── HEADER BANNER ── */
        .header {
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            padding: 0;
        }

        .header-inner {
            display: table;
            width: 100%;
            min-height: 52mm;
            background: linear-gradient(
                <?php echo e($isAr ? '270deg' : '90deg'); ?>,
                rgba(0,0,0,.55) 0%,
                rgba(0,0,0,.15) 60%,
                transparent 100%
            );
        }

        .header-left {
            display: table-cell;
            vertical-align: middle;
            <?php if($isAr): ?>
            padding: 12mm 20px 12mm 12px;
            text-align: right;
            <?php else: ?>
            padding: 12mm 12px 12mm 20px;
            text-align: left;
            <?php endif; ?>
        }

        .header-kicker {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: rgba(255,255,255,.75);
            margin-bottom: 5px;
        }

        .event-name {
            font-size: 18px;
            font-weight: bold;
            color: #fff;
            margin-bottom: 8px;
            line-height: 1.3;
        }

        .ticket-badge {
            display: inline-block;
            background: rgba(255,255,255,.18);
            border: 1px solid rgba(255,255,255,.5);
            color: #fff;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .header-right {
            display: table-cell;
            vertical-align: middle;
            width: 90px;
            text-align: center;
            <?php if($isAr): ?>
            padding: 12mm 20px 12mm 12px;
            <?php else: ?>
            padding: 12mm 20px 12mm 12px;
            <?php endif; ?>
        }

        .header-right img {
            width: 68px;
            height: 68px;
            border-radius: 8px;
            border: 3px solid rgba(255,255,255,.85);
            padding: 3px;
            background: #fff;
            display: block;
            margin: 0 auto 4px;
        }

        .qr-caption {
            font-size: 7px;
            color: rgba(255,255,255,.8);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ── TEAR LINE ── */
        .tear-line {
            display: table;
            width: 100%;
            background: #f3f4f6;
        }

        .tear-line-left {
            display: table-cell;
            width: 16px;
        }

        .tear-line-right {
            display: table-cell;
            width: 16px;
        }

        .tear-line-notch {
            width: 16px;
            height: 16px;
            background: #fff;
            border-radius: 50%;
        }

        .tear-line-left .tear-line-notch {
            border-top-right-radius: 50%;
            border-bottom-right-radius: 50%;
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }

        .tear-line-right .tear-line-notch {
            border-top-left-radius: 50%;
            border-bottom-left-radius: 50%;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        .tear-line-dash {
            display: table-cell;
            border-top: 1.5px dashed #9ca3af;
            vertical-align: middle;
        }

        /* ── BODY ── */
        .ticket-body {
            padding: 14px 20px 16px;
            background: #fff;
        }

        /* ── TWO-COLUMN GRID ── */
        .two-col {
            display: table;
            width: 100%;
            table-layout: fixed;
            margin-bottom: 14px;
        }

        .col {
            display: table-cell;
            vertical-align: top;
        }

        .col-divider {
            display: table-cell;
            width: 1px;
            background: #e5e7eb;
            padding: 0 10px;
        }

        .col-divider-inner {
            width: 1px;
            background: #e5e7eb;
            height: 100%;
        }

        /* ── SECTION ── */
        .section-label {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #667eea;
            border-bottom: 1.5px solid #667eea;
            padding-bottom: 4px;
            margin-bottom: 9px;
            text-align: <?php echo e($isAr ? 'right' : 'left'); ?>;
        }

        /* ── INFO ROWS ── */
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 4px 0;
            vertical-align: top;
        }

        .info-key {
            font-weight: 600;
            color: #6b7280;
            font-size: 10px;
            width: 38%;
            text-align: <?php echo e($isAr ? 'right' : 'left'); ?>;
            <?php if($isAr): ?>
            padding-left: 8px;
            <?php else: ?>
            padding-right: 8px;
            <?php endif; ?>
        }

        .info-val {
            color: #1f2937;
            font-size: 11px;
            text-align: <?php echo e($isAr ? 'right' : 'left'); ?>;
        }

        /* ── SERVICES ── */
        .services-list {
            list-style: none;
        }

        .services-list li {
            padding: 5px 0;
            border-bottom: 1px dashed #e5e7eb;
            text-align: <?php echo e($isAr ? 'right' : 'left'); ?>;
            font-size: 10px;
        }

        .services-list li:last-child {
            border-bottom: none;
        }

        .svc-qty {
            color: #9ca3af;
            font-size: 9px;
        }

        /* ── QR ENTRY STRIP ── */
        .entry-strip {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px 16px;
            display: table;
            width: 100%;
            margin-top: 14px;
        }

        .entry-qr-cell {
            display: table-cell;
            vertical-align: middle;
            width: 90px;
            text-align: center;
        }

        .entry-qr-cell img {
            width: 72px;
            height: 72px;
            padding: 4px;
            background: #fff;
            border: 1px solid #d1d5db;
            border-radius: 6px;
        }

        .entry-info-cell {
            display: table-cell;
            vertical-align: middle;
            <?php if($isAr): ?>
            padding-right: 14px;
            text-align: right;
            <?php else: ?>
            padding-left: 14px;
            text-align: left;
            <?php endif; ?>
        }

        .entry-label {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #9ca3af;
            margin-bottom: 4px;
        }

        .entry-ticket-num {
            font-family: 'Courier New', monospace;
            font-size: 15px;
            font-weight: bold;
            color: #14532d;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }

        .entry-note {
            font-size: 10px;
            color: #6b7280;
            line-height: 1.4;
        }

        /* ── FOOTER ── */
        .ticket-footer {
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            padding: 9px 20px;
            display: table;
            width: 100%;
        }

        .footer-ref {
            display: table-cell;
            vertical-align: middle;
            text-align: <?php echo e($isAr ? 'right' : 'left'); ?>;
        }

        .footer-copy {
            display: table-cell;
            vertical-align: middle;
            text-align: <?php echo e($isAr ? 'left' : 'right'); ?>;
        }

        .footer-ref, .footer-copy {
            font-size: 9px;
            color: #9ca3af;
        }

        .footer-ref strong {
            color: #6b7280;
        }
    </style>
</head>
<body>
<div class="ticket">

    
    <div class="header" style="background-image: url('<?php echo e($headerBg); ?>');">
        <div class="header-inner">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isAr): ?>
            <div class="header-right">
                <img src="<?php echo e($qrCode); ?>" alt="QR">
                <div class="qr-caption"><?php echo e($t('scan_to_verify')); ?></div>
            </div>
            <div class="header-left">
                <div class="header-kicker"><?php echo e($t('event_ticket')); ?></div>
                <div class="event-name"><?php echo e($booking->event->getTranslation('title', 'ar')); ?></div>
                <span class="ticket-badge" dir="ltr"><?php echo e($attendee->ticket_number); ?></span>
            </div>
            <?php else: ?>
            <div class="header-left">
                <div class="header-kicker"><?php echo e($t('event_ticket')); ?></div>
                <div class="event-name"><?php echo e($booking->event->getTranslation('title', 'en')); ?></div>
                <span class="ticket-badge"><?php echo e($attendee->ticket_number); ?></span>
            </div>
            <div class="header-right">
                <img src="<?php echo e($qrCode); ?>" alt="QR">
                <div class="qr-caption"><?php echo e($t('scan_to_verify')); ?></div>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    
    <div class="tear-line">
        <div class="tear-line-left"><div class="tear-line-notch"></div></div>
        <div class="tear-line-dash"></div>
        <div class="tear-line-right"><div class="tear-line-notch"></div></div>
    </div>

    
    <div class="ticket-body">

        
        <div class="two-col">
            
            <div class="col">
                <div class="section-label"><?php echo e($t('attendee')); ?></div>
                <table class="info-table">
                    <tr>
                        <td class="info-key"><?php echo e($t('name')); ?></td>
                        <td class="info-val"><?php echo e($attendee->getFullName()); ?></td>
                    </tr>
                    <tr>
                        <td class="info-key"><?php echo e($t('email')); ?></td>
                        <td class="info-val" style="word-break:break-all;"><?php echo e($attendee->email); ?></td>
                    </tr>
                    <tr>
                        <td class="info-key"><?php echo e($t('ticket_type')); ?></td>
                        <td class="info-val"><?php echo e($booking->ticketType->getTranslation('name', $lang)); ?></td>
                    </tr>
                </table>
            </div>

            
            <div class="col-divider"><div class="col-divider-inner"></div></div>

            
            <div class="col" style="<?php echo e($isAr ? 'padding-right:16px' : 'padding-left:16px'); ?>">
                <div class="section-label"><?php echo e($t('event_details')); ?></div>
                <table class="info-table">
                    <tr>
                        <td class="info-key"><?php echo e($t('date')); ?></td>
                        <td class="info-val"><?php echo e($dateFormatted); ?></td>
                    </tr>
                    <tr>
                        <td class="info-key"><?php echo e($t('time')); ?></td>
                        <td class="info-val"><?php echo e($booking->timeSlot->getTimeRange()); ?></td>
                    </tr>
                    <tr>
                        <td class="info-key"><?php echo e($t('location')); ?></td>
                        <td class="info-val"><?php echo e($booking->event->getTranslation('location', $lang)); ?></td>
                    </tr>
                    <tr>
                        <td class="info-key"><?php echo e($t('organizer')); ?></td>
                        <td class="info-val"><?php echo e($booking->event->organizer); ?></td>
                    </tr>
                </table>
            </div>
        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->extraServices->count() > 0): ?>
        <div style="margin-bottom:14px;">
            <div class="section-label"><?php echo e($t('extra_services')); ?></div>
            <ul class="services-list">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $booking->extraServices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li>
                    <strong><?php echo e($service->getTranslation('name', $lang)); ?></strong>
                    &nbsp;<span class="svc-qty">× <?php echo e($service->pivot->quantity); ?></span>
                </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </ul>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <div class="entry-strip">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isAr): ?>
            <div class="entry-info-cell">
                <div class="entry-label"><?php echo e($t('entry_pass')); ?></div>
                <div class="entry-ticket-num" dir="ltr"><?php echo e($attendee->ticket_number); ?></div>
                <div class="entry-note"><?php echo e($t('present_qr')); ?></div>
            </div>
            <div class="entry-qr-cell">
                <img src="<?php echo e($qrCode); ?>" alt="QR">
            </div>
            <?php else: ?>
            <div class="entry-qr-cell">
                <img src="<?php echo e($qrCode); ?>" alt="QR">
            </div>
            <div class="entry-info-cell">
                <div class="entry-label"><?php echo e($t('entry_pass')); ?></div>
                <div class="entry-ticket-num"><?php echo e($attendee->ticket_number); ?></div>
                <div class="entry-note"><?php echo e($t('present_qr')); ?></div>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

    </div>

    
    <div class="ticket-footer">
        <div class="footer-ref">
            <strong><?php echo e($t('booking_reference')); ?>:</strong>
            <span dir="ltr"> <?php echo e($booking->booking_reference); ?></span>
            &nbsp;|&nbsp;
            <strong><?php echo e($t('booked_on')); ?>:</strong>
            <span dir="ltr"> <?php echo e($booking->created_at->format('M d, Y H:i')); ?></span>
        </div>
        <div class="footer-copy">
            &copy; <?php echo e(date('Y')); ?> <?php echo e(config('app.name')); ?>. <?php echo e($t('all_rights')); ?>

        </div>
    </div>

</div>
</body>
</html>
<?php /**PATH C:\Apache24\htdocs\bookings\resources\views\tickets\individual.blade.php ENDPATH**/ ?>