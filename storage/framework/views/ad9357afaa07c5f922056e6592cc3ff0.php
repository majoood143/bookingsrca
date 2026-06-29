<?php
    $isRtl = $locale === 'ar';
    $chartJs = file_get_contents(public_path('vendor/chartjs/chart.umd.js'));

    $genderLabels = $byGender->pluck('label')->values();
    $genderData = $byGender->pluck('count')->values();
    $genderColors = ['#10b981', '#f59e0b', '#9ca3af'];

    $ticketLabels = $byTicket->pluck('label')->values();
    $ticketData = $byTicket->pluck('count')->values();

    $slotLabels = $byTimeSlot->pluck('label')->values();
    $slotData = $byTimeSlot->pluck('count')->values();

    $countryLabels = $byCountry->pluck('label')->values();
    $countryData = $byCountry->pluck('count')->values();
?>
<!DOCTYPE html>
<html dir="<?php echo e($isRtl ? 'rtl' : 'ltr'); ?>">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
        font-size: 12px;
        color: #1f2937;
    }
    .section { margin-bottom: 22px; }
    .section-title {
        font-size: 15px;
        font-weight: 700;
        color: #065f46;
        border-bottom: 2px solid #065f46;
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
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 8px;
        padding: 12px;
        text-align: center;
    }
    .stat-value { font-size: 20px; font-weight: 700; color: #047857; }
    .stat-label { font-size: 9px; color: #6b7280; text-transform: uppercase; margin-top: 4px; }

    .chart-wrap {
        width: 100%;
        height: 230px;
        margin-bottom: 14px;
    }

    table { width: 100%; border-collapse: collapse; font-size: 11px; }
    th {
        background: #065f46;
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
    .page-break { page-break-before: always; }
</style>
</head>
<body>

    <div class="section">
        <div class="section-title"><?php echo e(__('visitor_report.sections.summary', [], $locale)); ?></div>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo e($totalVisitors); ?></div>
                <div class="stat-label"><?php echo e(__('visitor_report.stats.total_visitors', [], $locale)); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo e($totalBookings); ?></div>
                <div class="stat-label"><?php echo e(__('visitor_report.stats.total_bookings', [], $locale)); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo e($checkedInCount); ?></div>
                <div class="stat-label"><?php echo e(__('visitor_report.stats.checked_in', [], $locale)); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo e($eventsCovered); ?></div>
                <div class="stat-label"><?php echo e(__('visitor_report.stats.events_covered', [], $locale)); ?></div>
            </div>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($totalVisitors === 0): ?>
        <p><?php echo e(__('visitor_report.no_data', [], $locale)); ?></p>
    <?php else: ?>

    <div class="section">
        <div class="section-title"><?php echo e(__('visitor_report.sections.by_gender', [], $locale)); ?></div>
        <div class="chart-wrap"><canvas id="genderChart"></canvas></div>
        <table>
            <thead>
                <tr>
                    <th><?php echo e(__('visitor_report.columns.gender', [], $locale)); ?></th>
                    <th class="text-center"><?php echo e(__('visitor_report.columns.count', [], $locale)); ?></th>
                    <th class="text-center"><?php echo e(__('visitor_report.columns.percentage', [], $locale)); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $byGender; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($row['label']); ?></td>
                        <td class="text-center"><?php echo e($row['count']); ?></td>
                        <td class="text-center"><?php echo e($row['percentage']); ?>%</td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="section page-break">
        <div class="section-title"><?php echo e(__('visitor_report.sections.by_ticket', [], $locale)); ?></div>
        <div class="chart-wrap"><canvas id="ticketChart"></canvas></div>
        <table>
            <thead>
                <tr>
                    <th><?php echo e(__('visitor_report.columns.ticket_type', [], $locale)); ?></th>
                    <th class="text-center"><?php echo e(__('visitor_report.columns.count', [], $locale)); ?></th>
                    <th class="text-center"><?php echo e(__('visitor_report.columns.percentage', [], $locale)); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $byTicket; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($row['label']); ?></td>
                        <td class="text-center"><?php echo e($row['count']); ?></td>
                        <td class="text-center"><?php echo e($row['percentage']); ?>%</td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="section page-break">
        <div class="section-title"><?php echo e(__('visitor_report.sections.by_time_slot', [], $locale)); ?></div>
        <div class="chart-wrap"><canvas id="slotChart"></canvas></div>
        <table>
            <thead>
                <tr>
                    <th><?php echo e(__('visitor_report.columns.time_slot', [], $locale)); ?></th>
                    <th class="text-center"><?php echo e(__('visitor_report.columns.count', [], $locale)); ?></th>
                    <th class="text-center"><?php echo e(__('visitor_report.columns.percentage', [], $locale)); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $byTimeSlot; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($row['label']); ?></td>
                        <td class="text-center"><?php echo e($row['count']); ?></td>
                        <td class="text-center"><?php echo e($row['percentage']); ?>%</td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="section page-break">
        <div class="section-title"><?php echo e(__('visitor_report.sections.by_country', [], $locale)); ?></div>
        <div class="chart-wrap"><canvas id="countryChart"></canvas></div>
        <table>
            <thead>
                <tr>
                    <th><?php echo e(__('visitor_report.columns.country', [], $locale)); ?></th>
                    <th class="text-center"><?php echo e(__('visitor_report.columns.count', [], $locale)); ?></th>
                    <th class="text-center"><?php echo e(__('visitor_report.columns.percentage', [], $locale)); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $byCountry; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($row['label']); ?></td>
                        <td class="text-center"><?php echo e($row['count']); ?></td>
                        <td class="text-center"><?php echo e($row['percentage']); ?>%</td>
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

    <?php if($totalVisitors > 0): ?>
    new Chart(document.getElementById('genderChart'), {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($genderLabels); ?>,
            datasets: [{ data: <?php echo json_encode($genderData); ?>, backgroundColor: <?php echo json_encode($genderColors); ?> }]
        },
        options: { plugins: { legend: { position: 'bottom' } } }
    });

    new Chart(document.getElementById('ticketChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($ticketLabels); ?>,
            datasets: [{ label: '<?php echo e(__('visitor_report.columns.count', [], $locale)); ?>', data: <?php echo json_encode($ticketData); ?>, backgroundColor: '#059669' }]
        },
        options: { indexAxis: 'y', plugins: { legend: { display: false } } }
    });

    new Chart(document.getElementById('slotChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($slotLabels); ?>,
            datasets: [{ label: '<?php echo e(__('visitor_report.columns.count', [], $locale)); ?>', data: <?php echo json_encode($slotData); ?>, backgroundColor: '#0891b2' }]
        },
        options: { plugins: { legend: { display: false } } }
    });

    new Chart(document.getElementById('countryChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($countryLabels); ?>,
            datasets: [{ label: '<?php echo e(__('visitor_report.columns.count', [], $locale)); ?>', data: <?php echo json_encode($countryData); ?>, backgroundColor: '#7c3aed' }]
        },
        options: { indexAxis: 'y', plugins: { legend: { display: false } } }
    });
    <?php endif; ?>

    setTimeout(function () { window.pdfReady = true; }, 250);
</script>

</body>
</html>
<?php /**PATH C:\Apache24\htdocs\bookings\resources\views/reports/visitor-report-pdf.blade.php ENDPATH**/ ?>