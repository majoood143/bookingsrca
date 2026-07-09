<div class="space-y-4 max-h-[70vh] overflow-y-auto">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-xs font-semibold uppercase <?php echo e($log->gateway === 'thawani' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700'); ?>">
                        <?php echo e($log->gateway); ?>

                    </span>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">
                        <?php echo e(\Illuminate\Support\Str::of($log->event)->replace('_', ' ')->headline()); ?>

                    </span>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($log->status_code): ?>
                        <span class="text-xs px-2 py-0.5 rounded <?php echo e($log->status_code >= 200 && $log->status_code < 300 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'); ?>">
                            HTTP <?php echo e($log->status_code); ?>

                        </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <span class="text-xs text-gray-500"><?php echo e($log->created_at->format('M d, Y H:i:s')); ?></span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <div class="text-xs font-semibold text-gray-500 mb-1"><?php echo e(__('booking.gateway_logs.request')); ?></div>
                    <pre class="text-xs bg-gray-50 dark:bg-gray-800 rounded p-2 overflow-x-auto whitespace-pre-wrap"><?php echo e(json_encode($log->request_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)); ?></pre>
                </div>
                <div>
                    <div class="text-xs font-semibold text-gray-500 mb-1"><?php echo e(__('booking.gateway_logs.response')); ?></div>
                    <pre class="text-xs bg-gray-50 dark:bg-gray-800 rounded p-2 overflow-x-auto whitespace-pre-wrap"><?php echo e(json_encode($log->response_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)); ?></pre>
                </div>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="text-center text-gray-500 py-8">
            <?php echo e(__('booking.gateway_logs.empty')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /Users/majidalhajri/Downloads/booking/bookingrca/bookingsrca/resources/views/filament/modals/payment-gateway-logs.blade.php ENDPATH**/ ?>