<?php
    $isRtl = $locale === 'ar';
    $chartJs = file_get_contents(public_path('vendor/chartjs/chart.umd.js'));
    $fmt = fn($n) => view('partials.currency-amount', ['amount' => (float) $n])->render();

    $eventLabels = $byEvent->pluck('event')->values();
    $eventData = $byEvent->pluck('revenue')->values();

    $ticketLabels = $byTicket->pluck('ticket_type')->values();
    $ticketData = $byTicket->pluck('revenue')->values();
?>
<!DOCTYPE html>
<html dir="<?php echo e($isRtl ? 'rtl' : 'ltr'); ?>">
<head>
<meta charset="UTF-8">
<?php echo $__env->make('reports.partials.pdf-fonts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'Almarai', 'Segoe UI', Tahoma, Arial, sans-serif;
        font-size: 12px;
        color: #1f2937;
    }
    .section { margin-bottom: 22px; }
    .section-title {
        font-size: 15px;
        font-weight: 700;
        color: #1e3a8a;
        border-bottom: 2px solid #1e3a8a;
        padding-bottom: 6px;
        margin-bottom: 14px;
    }
    .stats-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 8px;
    }
    .stat-card {
        flex: 1;
        min-width: 18%;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        padding: 12px;
        text-align: center;
    }
    .stat-value { font-size: 17px; font-weight: 700; color: #1d4ed8; }
    .stat-label { font-size: 9px; color: #6b7280; text-transform: uppercase; margin-top: 4px; }

    .chart-wrap {
        width: 100%;
        height: 230px;
        margin-bottom: 14px;
    }

    table { width: 100%; border-collapse: collapse; font-size: 11px; }
    th {
        background: #1e3a8a;
        color: #ffffff;
        padding: 7px 10px;
        text-align: <?php echo e($isRtl ? 'right' : 'left'); ?>;
        font-weight: 600;
    }
    td {
        padding: 6px 10px;
        border-bottom: 1px solid #e5e7eb;
    }
    tr:nth-child(even) td { background: #f9fafb; }
    .text-center { text-align: center; }
    .text-end { text-align: <?php echo e($isRtl ? 'left' : 'right'); ?>; }
    .page-break { page-break-before: always; }
</style>
</head>
<body>

    <div class="section">
        <div class="section-title"><?php echo e(__('reports.sections.summary', [], $locale)); ?></div>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo e($totalBookings); ?></div>
                <div class="stat-label"><?php echo e(__('reports.stats.total_bookings', [], $locale)); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo e($confirmedCount); ?></div>
                <div class="stat-label"><?php echo e(__('reports.stats.confirmed', [], $locale)); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo e($pendingCount); ?></div>
                <div class="stat-label"><?php echo e(__('reports.stats.pending', [], $locale)); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo e($cancelledCount); ?></div>
                <div class="stat-label"><?php echo e(__('reports.stats.cancelled', [], $locale)); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo e($checkedInCount); ?></div>
                <div class="stat-label"><?php echo e(__('reports.stats.checked_in', [], $locale)); ?></div>
            </div>
        </div>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo e($totalAttendees); ?></div>
                <div class="stat-label"><?php echo e(__('reports.stats.total_attendees', [], $locale)); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $fmt($totalRevenue); ?></div>
                <div class="stat-label"><?php echo e(__('reports.stats.total_revenue', [], $locale)); ?></div>
            </div>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($totalBookings === 0): ?>
        <p><?php echo e(__('reports.no_data', [], $locale)); ?></p>
    <?php else: ?>

    <div class="section">
        <div class="section-title"><?php echo e(__('reports.sections.by_event', [], $locale)); ?></div>
        <div class="chart-wrap"><canvas id="eventChart"></canvas></div>
        <table>
            <thead>
                <tr>
                    <th><?php echo e(__('reports.columns.event', [], $locale)); ?></th>
                    <th class="text-center"><?php echo e(__('reports.columns.total', [], $locale)); ?></th>
                    <th class="text-center"><?php echo e(__('reports.columns.confirmed', [], $locale)); ?></th>
                    <th class="text-center"><?php echo e(__('reports.columns.pending', [], $locale)); ?></th>
                    <th class="text-center"><?php echo e(__('reports.columns.cancelled', [], $locale)); ?></th>
                    <th class="text-center"><?php echo e(__('reports.columns.checked_in', [], $locale)); ?></th>
                    <th class="text-center"><?php echo e(__('reports.columns.attendees', [], $locale)); ?></th>
                    <th class="text-end"><?php echo e(__('reports.columns.revenue', [], $locale)); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $byEvent; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($row['event']); ?></td>
                        <td class="text-center"><?php echo e($row['total']); ?></td>
                        <td class="text-center"><?php echo e($row['confirmed']); ?></td>
                        <td class="text-center"><?php echo e($row['pending']); ?></td>
                        <td class="text-center"><?php echo e($row['cancelled']); ?></td>
                        <td class="text-center"><?php echo e($row['checked_in']); ?></td>
                        <td class="text-center"><?php echo e($row['attendees']); ?></td>
                        <td class="text-end"><?php echo $fmt($row['revenue']); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="section page-break">
        <div class="section-title"><?php echo e(__('reports.sections.by_ticket', [], $locale)); ?></div>
        <div class="chart-wrap"><canvas id="ticketChart"></canvas></div>
        <table>
            <thead>
                <tr>
                    <th><?php echo e(__('reports.columns.ticket_type', [], $locale)); ?></th>
                    <th class="text-center"><?php echo e(__('reports.columns.bookings', [], $locale)); ?></th>
                    <th class="text-center"><?php echo e(__('reports.columns.attendees', [], $locale)); ?></th>
                    <th class="text-end"><?php echo e(__('reports.columns.revenue', [], $locale)); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $byTicket; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($row['ticket_type']); ?></td>
                        <td class="text-center"><?php echo e($row['bookings']); ?></td>
                        <td class="text-center"><?php echo e($row['attendees']); ?></td>
                        <td class="text-end"><?php echo $fmt($row['revenue']); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<script><?php echo $chartJs; ?></script>
<script>
    Chart.defaults.animation = false;
    Chart.defaults.font.size = 11;

    <?php if($totalBookings > 0): ?>
    new Chart(document.getElementById('eventChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($eventLabels); ?>,
            datasets: [{ label: '<?php echo e(__('reports.columns.revenue', [], $locale)); ?>', data: <?php echo json_encode($eventData); ?>, backgroundColor: '#1d4ed8' }]
        },
        options: { indexAxis: 'y', plugins: { legend: { display: false } } }
    });

    new Chart(document.getElementById('ticketChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($ticketLabels); ?>,
            datasets: [{ label: '<?php echo e(__('reports.columns.revenue', [], $locale)); ?>', data: <?php echo json_encode($ticketData); ?>, backgroundColor: '#0891b2' }]
        },
        options: { indexAxis: 'y', plugins: { legend: { display: false } } }
    });
    <?php endif; ?>

    setTimeout(function () { window.pdfReady = true; }, 250);
</script>

</body>
</html>
<?php /**PATH /Users/majidalhajri/Downloads/booking/bookingrca/bookingsrca/resources/views/reports/reports-pdf.blade.php ENDPATH**/ ?>