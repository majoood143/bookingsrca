<?php $__env->startSection('code', '403'); ?>
<?php $__env->startSection('title', __('errors.403.title')); ?>
<?php $__env->startSection('heading', __('errors.403.heading')); ?>
<?php $__env->startSection('description', __('errors.403.description')); ?>

<?php $__env->startSection('actions'); ?>
    <a href="<?php echo e(url('/')); ?>" class="btn btn-primary"><?php echo e(__('errors.back_home')); ?></a>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('errors.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/majidalhajri/Downloads/booking/bookingrca/bookingsrca/resources/views/errors/403.blade.php ENDPATH**/ ?>