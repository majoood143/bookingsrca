<?php
    $progress = (int) $getState() ?? 0;
    $color = match(true) {
        $progress >= 100 => 'bg-success-500',
        $progress >= 50 => 'bg-primary-500',
        $progress > 0 => 'bg-warning-500',
        default => 'bg-gray-300 dark:bg-gray-600',
    };
?>

<div class="flex items-center gap-2">
    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 overflow-hidden">
        <div class="<?php echo e($color); ?> h-2.5 rounded-full transition-all duration-300" style="width: <?php echo e($progress); ?>%"></div>
    </div>
    <span class="text-xs text-gray-600 dark:text-gray-400 whitespace-nowrap min-w-[2.5rem] text-right"><?php echo e($progress); ?>%</span>
</div>
<?php /**PATH C:\Apache24\htdocs\bookings\vendor\croustibat\filament-jobs-monitor\resources\views\columns\progress-bar.blade.php ENDPATH**/ ?>