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
        /* "size" is intentionally omitted: this mPDF version mis-handles a
           named @page size (e.g. "A4 portrait"), causing runaway pagination
           (hundreds of extra pages) even for trivial content. Page format is
           already set via the Mpdf 'format' constructor option in PHP. */
        @page {
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
        /* The artwork is a full card graphic (green band / white pass area /
           green band) with a blank bleed margin below it. Cropping to 70mm
           (background-position:top + overflow:hidden) keeps both green bands
           intact at the container's aspect ratio and trims only that trailing
           blank margin. Content sits in a REAL <table> (see the two-col
           section below for the same fix) — CSS display:table/table-cell on
           divs renders each "cell" as a stacked full-width block in this mPDF
           version instead of laying them out side by side, and its
           vertical-align:middle is unreliable outside real table cells. */
        .header {
            background-size: 100% auto;
            background-position: top center;
            background-repeat: no-repeat;
            height: 70mm;
            overflow: hidden;
            padding: 0;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        /* Content is top-offset with padding-top (not vertical-align:middle —
           mPDF sizes the table row to its content, not to the .header's fixed
           height, so "centering" would run off a row far shorter than 70mm)
           to land just below the artwork's top green band, inside the white
           pass area. */
        .header-left {
            text-align: <?php echo e($isAr ? 'right' : 'left'); ?>;
            <?php if($isAr): ?>
            padding: 25mm 16px 0 12px;
            <?php else: ?>
            padding: 25mm 12px 0 16px;
            <?php endif; ?>
        }

        .header-kicker {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #6b8f7a;
            margin-bottom: 4px;
        }

        .attendee-name {
            font-size: 19px;
            font-weight: bold;
            color: #14532d;
            margin-bottom: 4px;
            line-height: 1.25;
        }

        .event-line {
            font-size: 10px;
            color: #4b5563;
            margin-bottom: 3px;
        }

        .event-meta {
            font-size: 9px;
            color: #6b7280;
            margin-bottom: 9px;
        }

        .ticket-badge {
            display: inline-block;
            background: #14532d;
            color: #fff;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .header-right {
            width: 76px;
            text-align: center;
            <?php if($isAr): ?>
            padding: 25mm 12px 0 16px;
            <?php else: ?>
            padding: 25mm 16px 0 12px;
            <?php endif; ?>
        }

        .header-right img {
            width: 62px;
            height: 62px;
            border-radius: 8px;
            border: 2px solid #e5e7eb;
            padding: 3px;
            background: #fff;
            display: block;
            margin: 0 auto 4px;
        }

        .qr-caption {
            font-size: 7px;
            color: #9ca3af;
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
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .col {
            width: 49.5%;
            vertical-align: top;
        }

        .col-divider {
            width: 1px;
            background: #e5e7eb;
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
        <table class="header-table">
            <tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isAr): ?>
                <td class="header-right" width="76">
                    <img src="<?php echo e($qrCode); ?>" width="120" height="120" alt="QR">
                    <div class="qr-caption"><?php echo e($t('scan_to_verify')); ?></div>
                </td>
                <td class="header-left">
                    <div class="header-kicker"><?php echo e($t('attendee')); ?></div>
                    <div class="attendee-name"><?php echo e($attendee->getFullName()); ?></div>
                    <div class="event-line"><?php echo e($booking->event->getTranslation('title', 'ar')); ?> &nbsp;·&nbsp; <?php echo e($dateFormatted); ?></div>
                    <div class="event-meta"><?php echo e($t('time')); ?> <span dir="ltr"><?php echo e($booking->timeSlot->getTimeRange()); ?></span> &nbsp;·&nbsp; <?php echo e($t('location')); ?> <?php echo e($booking->event->getTranslation('location', 'ar')); ?></div>
                    <span class="ticket-badge" dir="ltr"><?php echo e($attendee->ticket_number); ?></span>
                </td>
                <?php else: ?>
                <td class="header-left">
                    <div class="header-kicker"><?php echo e($t('attendee')); ?></div>
                    <div class="attendee-name"><?php echo e($attendee->getFullName()); ?></div>
                    <div class="event-line"><?php echo e($booking->event->getTranslation('title', 'en')); ?> &nbsp;·&nbsp; <?php echo e($dateFormatted); ?></div>
                    <div class="event-meta"><?php echo e($t('time')); ?> <?php echo e($booking->timeSlot->getTimeRange()); ?> &nbsp;·&nbsp; <?php echo e($t('location')); ?> <?php echo e($booking->event->getTranslation('location', 'en')); ?></div>
                    <span class="ticket-badge"><?php echo e($attendee->ticket_number); ?></span>
                </td>
                <td class="header-right" width="76">
                    <img src="<?php echo e($qrCode); ?>" width="120" height="120" alt="QR">
                    <div class="qr-caption"><?php echo e($t('scan_to_verify')); ?></div>
                </td>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tr>
        </table>
    </div>

    
    <div class="tear-line">
        <div class="tear-line-left"><div class="tear-line-notch"></div></div>
        <div class="tear-line-dash"></div>
        <div class="tear-line-right"><div class="tear-line-notch"></div></div>
    </div>

    
    <div class="ticket-body">

        
        
        <table class="two-col">
            <tr>
                <td class="col">
                    <div class="section-label"><?php echo e($t('attendee')); ?></div>
                    <table class="info-table">
                        <tr>
                            <td class="info-key"><?php echo e($t('email')); ?></td>
                            <td class="info-val" style="word-break:break-all;"><?php echo e($attendee->email); ?></td>
                        </tr>
                        <tr>
                            <td class="info-key"><?php echo e($t('ticket_type')); ?></td>
                            <td class="info-val"><?php echo e($booking->ticketType->getTranslation('name', $lang)); ?></td>
                        </tr>
                    </table>
                </td>
                <td class="col-divider"></td>
                <td class="col" style="<?php echo e($isAr ? 'padding-right:16px' : 'padding-left:16px'); ?>">
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
                </td>
            </tr>
        </table>

        
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
<?php /**PATH /Users/majidalhajri/Downloads/booking/bookingrca/bookingsrca/resources/views/tickets/individual.blade.php ENDPATH**/ ?>