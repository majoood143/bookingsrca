<?php $__env->startSection('code', '503'); ?>
<?php $__env->startSection('title', __('errors.503.title')); ?>
<?php $__env->startSection('heading', __('errors.503.heading')); ?>
<?php $__env->startSection('description', __('errors.503.description')); ?>

<?php $__env->startSection('actions'); ?>
    <a href="javascript:window.location.reload()" class="btn btn-primary"><?php echo e(__('errors.refresh')); ?></a>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('errors.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/majidalhajri/Downloads/booking/bookingrca/bookingsrca/resources/views/errors/503.blade.php ENDPATH**/ ?>