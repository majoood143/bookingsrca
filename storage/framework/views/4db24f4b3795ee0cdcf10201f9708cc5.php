<?php
    $isRtl = $locale === 'ar';
    $chartJs = file_get_contents(public_path('vendor/chartjs/chart.umd.js'));
    $currency = \App\Models\BookingSetting::get('currency_code') ?: __('financial_report.currency', [], $locale);

    $trendLabels = $revenueTrend->pluck('date')->values();
    $trendData = $revenueTrend->pluck('revenue')->values();

    $ticketLabels = $byTicket->pluck('label')->values();
    $ticketData = $byTicket->pluck('revenue')->values();

    $eventLabels = $byEvent->pluck('label')->values();
    $eventData = $byEvent->pluck('revenue')->values();

    $methodLabels = $byPaymentMethod->pluck('label')->values();
    $methodData = $byPaymentMethod->pluck('amount')->values();
    $methodColors = ['#059669', '#0891b2', '#f59e0b', '#9ca3af', '#7c3aed'];

    $fmt = fn($n) => number_format((float) $n, 3) . ' ' . $currency;
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
        color: #92400e;
        border-bottom: 2px solid #92400e;
        padding-bottom: 6px;
        margin-bottom: 14px;
    }
    .stats-grid {
        display: flex;
        gap: 10px;
        margin-bottom: 8px;
    }
    .stat-card {
        flex: 1;
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 8px;
        padding: 12px;
        text-align: center;
    }
    .stat-value { font-size: 17px; font-weight: 700; color: #b45309; }
    .stat-label { font-size: 9px; color: #6b7280; text-transform: uppercase; margin-top: 4px; }

    .chart-wrap {
        width: 100%;
        height: 230px;
        margin-bottom: 14px;
    }

    table { width: 100%; border-collapse: collapse; font-size: 11px; }
    th {
        background: #92400e;
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
        <div class="section-title"><?php echo e(__('financial_report.sections.summary', [], $locale)); ?></div>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo e($fmt($totalRevenue)); ?></div>
                <div class="stat-label"><?php echo e(__('financial_report.stats.total_revenue', [], $locale)); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo e($fmt($totalPaid)); ?></div>
                <div class="stat-label"><?php echo e(__('financial_report.stats.total_paid', [], $locale)); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo e($fmt($balanceDue)); ?></div>
                <div class="stat-label"><?php echo e(__('financial_report.stats.balance_due', [], $locale)); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo e($totalBookings); ?></div>
                <div class="stat-label"><?php echo e(__('financial_report.stats.total_bookings', [], $locale)); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo e($fmt($avgBooking)); ?></div>
                <div class="stat-label"><?php echo e(__('financial_report.stats.avg_booking', [], $locale)); ?></div>
            </div>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($totalBookings === 0): ?>
        <p><?php echo e(__('financial_report.no_data', [], $locale)); ?></p>
    <?php else: ?>

    <div class="section">
        <div class="section-title"><?php echo e(__('financial_report.sections.revenue_trend', [], $locale)); ?></div>
        <div class="chart-wrap"><canvas id="trendChart"></canvas></div>
    </div>

    <div class="section page-break">
        <div class="section-title"><?php echo e(__('financial_report.sections.by_ticket', [], $locale)); ?></div>
        <div class="chart-wrap"><canvas id="ticketChart"></canvas></div>
        <table>
            <thead>
                <tr>
                    <th><?php echo e(__('financial_report.columns.ticket_type', [], $locale)); ?></th>
                    <th class="text-center"><?php echo e(__('financial_report.columns.bookings', [], $locale)); ?></th>
                    <th class="text-end"><?php echo e(__('financial_report.columns.revenue', [], $locale)); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $byTicket; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($row['label']); ?></td>
                        <td class="text-center"><?php echo e($row['bookings']); ?></td>
                        <td class="text-end"><?php echo e($fmt($row['revenue'])); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="section page-break">
        <div class="section-title"><?php echo e(__('financial_report.sections.by_event', [], $locale)); ?></div>
        <div class="chart-wrap"><canvas id="eventChart"></canvas></div>
        <table>
            <thead>
                <tr>
                    <th><?php echo e(__('financial_report.columns.event', [], $locale)); ?></th>
                    <th class="text-center"><?php echo e(__('financial_report.columns.bookings', [], $locale)); ?></th>
                    <th class="text-end"><?php echo e(__('financial_report.columns.revenue', [], $locale)); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $byEvent; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($row['label']); ?></td>
                        <td class="text-center"><?php echo e($row['bookings']); ?></td>
                        <td class="text-end"><?php echo e($fmt($row['revenue'])); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="section page-break">
        <div class="section-title"><?php echo e(__('financial_report.sections.by_payment_method', [], $locale)); ?></div>
        <div class="chart-wrap"><canvas id="methodChart"></canvas></div>
        <table>
            <thead>
                <tr>
                    <th><?php echo e(__('financial_report.columns.payment_method', [], $locale)); ?></th>
                    <th class="text-end"><?php echo e(__('financial_report.columns.revenue', [], $locale)); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $byPaymentMethod; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($row['label']); ?></td>
                        <td class="text-end"><?php echo e($fmt($row['amount'])); ?></td>
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
    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode($trendLabels); ?>,
            datasets: [{
                label: '<?php echo e(__('financial_report.columns.revenue', [], $locale)); ?>',
                data: <?php echo json_encode($trendData); ?>,
                borderColor: '#b45309',
                backgroundColor: 'rgba(180, 83, 9, 0.15)',
                fill: true,
                tension: 0.25,
            }]
        },
        options: { plugins: { legend: { display: false } } }
    });

    new Chart(document.getElementById('ticketChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($ticketLabels); ?>,
            datasets: [{ label: '<?php echo e(__('financial_report.columns.revenue', [], $locale)); ?>', data: <?php echo json_encode($ticketData); ?>, backgroundColor: '#b45309' }]
        },
        options: { indexAxis: 'y', plugins: { legend: { display: false } } }
    });

    new Chart(document.getElementById('eventChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($eventLabels); ?>,
            datasets: [{ label: '<?php echo e(__('financial_report.columns.revenue', [], $locale)); ?>', data: <?php echo json_encode($eventData); ?>, backgroundColor: '#0891b2' }]
        },
        options: { indexAxis: 'y', plugins: { legend: { display: false } } }
    });

    new Chart(document.getElementById('methodChart'), {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($methodLabels); ?>,
            datasets: [{ data: <?php echo json_encode($methodData); ?>, backgroundColor: <?php echo json_encode($methodColors); ?> }]
        },
        options: { plugins: { legend: { position: 'bottom' } } }
    });
    <?php endif; ?>

    setTimeout(function () { window.pdfReady = true; }, 250);
</script>

</body>
</html>
<?php /**PATH /Users/majidalhajri/Downloads/booking/bookingrca/bookingsrca/resources/views/reports/financial-report-pdf.blade.php ENDPATH**/ ?>