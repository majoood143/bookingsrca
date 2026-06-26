<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Event Ticket</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
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
        }
        .ticket-info td {
            padding: 8px 0;
        }
        .ticket-info td:first-child {
            font-weight: bold;
            width: 40%;
        }
        .qr-section {
            text-align: center;
            margin: 30px 0;
        }
        .qr-section img {
            max-width: 200px;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
        .footer {
            background: #f5f5f5;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-radius: 0 0 10px 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Your Event Ticket</h1>
        <p><?php echo e($booking->event->getTranslation('title', 'en')); ?></p>
    </div>
    
    <div class="content">
        <h2>Hello <?php echo e($attendee->first_name); ?>!</h2>
        
        <p>Thank you for your booking! Your ticket is ready. Please find your ticket details below:</p>
        
        <div class="ticket-info">
            <table>
                <tr>
                    <td>Ticket Number:</td>
                    <td><strong><?php echo e($attendee->ticket_number); ?></strong></td>
                </tr>
                <tr>
                    <td>Attendee:</td>
                    <td><?php echo e($attendee->getFullName()); ?></td>
                </tr>
                <tr>
                    <td>Event:</td>
                    <td><?php echo e($booking->event->getTranslation('title', 'en')); ?></td>
                </tr>
                <tr>
                    <td>Date:</td>
                    <td><?php echo e($booking->event_date->format('l, F j, Y')); ?></td>
                </tr>
                <tr>
                    <td>Time:</td>
                    <td><?php echo e($booking->timeSlot->getTimeRange()); ?></td>
                </tr>
                <tr>
                    <td>Location:</td>
                    <td><?php echo e($booking->event->getTranslation('location', 'en')); ?></td>
                </tr>
                <tr>
                    <td>Ticket Type:</td>
                    <td><?php echo e($booking->ticketType->getTranslation('name', 'en')); ?></td>
                </tr>
            </table>
        </div>
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($booking->extraServices->count() > 0): ?>
            <h3>Extra Services Included:</h3>
            <ul>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $booking->extraServices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $service): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($service->getTranslation('name', 'en')); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </ul>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        
        <div class="qr-section">
            <h3>Your QR Code</h3>
            <img src="<?php echo e($attendee->getQrCodeBase64()); ?>" alt="QR Code">
            <p><strong>Important:</strong> Please present this QR code at the event entrance.</p>
        </div>
        
        <p><strong>Attachments:</strong></p>
        <ul>
            <li>📄 PDF Ticket (ticket-<?php echo e($attendee->ticket_number); ?>.pdf)</li>
            <li>📱 QR Code (qr-code-<?php echo e($attendee->ticket_number); ?>.png)</li>
        </ul>
        
        <p>We look forward to seeing you at the event!</p>
        
        <p style="margin-top: 30px;">
            <small><strong>Booking Reference:</strong> <?php echo e($booking->booking_reference); ?></small>
        </p>
    </div>
    
    <div class="footer">
        <p>This is an automated email. Please do not reply to this message.</p>
        <p>If you have any questions, please contact event support.</p>
        <p>&copy; <?php echo e(date('Y')); ?> <?php echo e(config('app.name')); ?>. All rights reserved.</p>
    </div>
</body>
</html>
<?php /**PATH C:\Apache24\htdocs\bookings\resources\views/emails/individual-ticket.blade.php ENDPATH**/ ?>