<?php
    $__currencyIcon = \App\Models\BookingSetting::currencyIconDataUri();
    $__currencySymbol = \App\Models\BookingSetting::currencySymbol();
?>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($__currencyIcon): ?>
    <img src="<?php echo e($__currencyIcon); ?>" alt="<?php echo e($__currencySymbol); ?>" style="display:inline-block;height:0.9em;width:0.9em;vertical-align:-0.05em;"><?php echo e(number_format($amount, 3)); ?>

<?php else: ?>
    <?php echo e($__currencySymbol); ?><?php echo e(number_format($amount, 3)); ?>

<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH /Users/majidalhajri/Downloads/booking/bookingrca/bookingsrca/resources/views/partials/currency-amount.blade.php ENDPATH**/ ?>