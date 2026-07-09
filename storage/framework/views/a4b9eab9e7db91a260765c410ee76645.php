<?php $__env->startSection('code', '500'); ?>
<?php $__env->startSection('title', __('errors.500.title')); ?>
<?php $__env->startSection('heading', __('errors.500.heading')); ?>
<?php $__env->startSection('description', __('errors.500.description')); ?>

<?php $__env->startSection('actions'); ?>
    <a href="javascript:window.location.reload()" class="btn btn-primary"><?php echo e(__('errors.try_again')); ?></a>
    <a href="<?php echo e(url('/')); ?>" class="btn btn-secondary"><?php echo e(__('errors.back_home')); ?></a>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('errors.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/majidalhajri/Downloads/booking/bookingrca/bookingsrca/resources/views/errors/500.blade.php ENDPATH**/ ?>