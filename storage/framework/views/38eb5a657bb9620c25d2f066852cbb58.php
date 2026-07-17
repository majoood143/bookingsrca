<?php if (isset($component)) { $__componentOriginal166a02a7c5ef5a9331faf66fa665c256 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-panels::components.page.index','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-panels::page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    
    <?php if (isset($component)) { $__componentOriginalee08b1367eba38734199cf7829b1d1e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalee08b1367eba38734199cf7829b1d1e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.section.index','data' => ['heading' => __('reports.sections.filters')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('reports.sections.filters'))]); ?>
        <form wire:submit.prevent>
            <?php echo e($this->form); ?>

        </form>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalee08b1367eba38734199cf7829b1d1e9)): ?>
<?php $attributes = $__attributesOriginalee08b1367eba38734199cf7829b1d1e9; ?>
<?php unset($__attributesOriginalee08b1367eba38734199cf7829b1d1e9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalee08b1367eba38734199cf7829b1d1e9)): ?>
<?php $component = $__componentOriginalee08b1367eba38734199cf7829b1d1e9; ?>
<?php unset($__componentOriginalee08b1367eba38734199cf7829b1d1e9); ?>
<?php endif; ?>

    <?php
        $report = $this->getReportData();
        $from   = $report['from'];
        $to     = $report['to'];
    ?>

    
    <p class="text-sm text-gray-500 dark:text-gray-400 -mt-2">
        <?php echo e(__('reports.period_label', ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')])); ?>

    </p>

    
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-7">

        <?php
            $stats = [
                ['label' => __('reports.stats.total_bookings'),  'value' => $report['totalBookings'],  'color' => 'bg-gray-100 dark:bg-gray-800',         'text' => 'text-gray-800 dark:text-gray-100'],
                ['label' => __('reports.stats.confirmed'),       'value' => $report['confirmedCount'], 'color' => 'bg-green-100 dark:bg-green-900/40',    'text' => 'text-green-700 dark:text-green-300'],
                ['label' => __('reports.stats.pending'),         'value' => $report['pendingCount'],   'color' => 'bg-amber-100 dark:bg-amber-900/40',    'text' => 'text-amber-700 dark:text-amber-300'],
                ['label' => __('reports.stats.cancelled'),       'value' => $report['cancelledCount'], 'color' => 'bg-red-100 dark:bg-red-900/40',        'text' => 'text-red-700 dark:text-red-300'],
                ['label' => __('reports.stats.checked_in'),      'value' => $report['checkedInCount'], 'color' => 'bg-blue-100 dark:bg-blue-900/40',      'text' => 'text-blue-700 dark:text-blue-300'],
                ['label' => __('reports.stats.total_attendees'), 'value' => $report['totalAttendees'], 'color' => 'bg-purple-100 dark:bg-purple-900/40',  'text' => 'text-purple-700 dark:text-purple-300'],
                ['label' => __('reports.stats.total_revenue'),   'value' => view('partials.currency-amount', ['amount' => (float)$report['totalRevenue']])->render(), 'color' => 'bg-emerald-100 dark:bg-emerald-900/40', 'text' => 'text-emerald-700 dark:text-emerald-300', 'html' => true],
            ];
        ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $stats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="rounded-xl p-4 <?php echo e($stat['color']); ?> flex flex-col gap-1 shadow-sm">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide leading-tight">
                    <?php echo e($stat['label']); ?>

                </span>
                <span class="text-2xl font-bold <?php echo e($stat['text']); ?>">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($stat['html'])): ?> <?php echo $stat['value']; ?> <?php else: ?> <?php echo e($stat['value']); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </span>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <?php if (isset($component)) { $__componentOriginalee08b1367eba38734199cf7829b1d1e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalee08b1367eba38734199cf7829b1d1e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.section.index','data' => ['heading' => __('reports.sections.by_event')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('reports.sections.by_event'))]); ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($report['byEvent']->isEmpty()): ?>
            <p class="text-sm text-gray-400 dark:text-gray-500 py-4 text-center">
                <?php echo e(__('reports.no_data')); ?>

            </p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="py-3 px-4 font-semibold text-gray-600 dark:text-gray-300"><?php echo e(__('reports.columns.event')); ?></th>
                            <th class="py-3 px-4 font-semibold text-gray-600 dark:text-gray-300 text-center"><?php echo e(__('reports.columns.total')); ?></th>
                            <th class="py-3 px-4 font-semibold text-green-600 dark:text-green-400 text-center"><?php echo e(__('reports.columns.confirmed')); ?></th>
                            <th class="py-3 px-4 font-semibold text-amber-600 dark:text-amber-400 text-center"><?php echo e(__('reports.columns.pending')); ?></th>
                            <th class="py-3 px-4 font-semibold text-red-600 dark:text-red-400 text-center"><?php echo e(__('reports.columns.cancelled')); ?></th>
                            <th class="py-3 px-4 font-semibold text-blue-600 dark:text-blue-400 text-center"><?php echo e(__('reports.columns.checked_in')); ?></th>
                            <th class="py-3 px-4 font-semibold text-gray-600 dark:text-gray-300 text-center"><?php echo e(__('reports.columns.attendees')); ?></th>
                            <th class="py-3 px-4 font-semibold text-emerald-600 dark:text-emerald-400 text-right"><?php echo e(__('reports.columns.revenue')); ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $report['byEvent']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="py-3 px-4 font-medium text-gray-800 dark:text-gray-200"><?php echo e($row['event']); ?></td>
                                <td class="py-3 px-4 text-center text-gray-700 dark:text-gray-300"><?php echo e($row['total']); ?></td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300">
                                        <?php echo e($row['confirmed']); ?>

                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                                        <?php echo e($row['pending']); ?>

                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300">
                                        <?php echo e($row['cancelled']); ?>

                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                                        <?php echo e($row['checked_in']); ?>

                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center text-gray-700 dark:text-gray-300"><?php echo e($row['attendees']); ?></td>
                                <td class="py-3 px-4 text-right font-semibold text-emerald-600 dark:text-emerald-400">
                                    <?php echo $__env->make('partials.currency-amount', ['amount' => (float)$row['revenue']], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalee08b1367eba38734199cf7829b1d1e9)): ?>
<?php $attributes = $__attributesOriginalee08b1367eba38734199cf7829b1d1e9; ?>
<?php unset($__attributesOriginalee08b1367eba38734199cf7829b1d1e9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalee08b1367eba38734199cf7829b1d1e9)): ?>
<?php $component = $__componentOriginalee08b1367eba38734199cf7829b1d1e9; ?>
<?php unset($__componentOriginalee08b1367eba38734199cf7829b1d1e9); ?>
<?php endif; ?>

    
    <?php if (isset($component)) { $__componentOriginalee08b1367eba38734199cf7829b1d1e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalee08b1367eba38734199cf7829b1d1e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.section.index','data' => ['heading' => __('reports.sections.by_ticket')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['heading' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('reports.sections.by_ticket'))]); ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($report['byTicket']->isEmpty()): ?>
            <p class="text-sm text-gray-400 dark:text-gray-500 py-4 text-center">
                <?php echo e(__('reports.no_data')); ?>

            </p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="py-3 px-4 font-semibold text-gray-600 dark:text-gray-300"><?php echo e(__('reports.columns.ticket_type')); ?></th>
                            <th class="py-3 px-4 font-semibold text-gray-600 dark:text-gray-300 text-center"><?php echo e(__('reports.columns.bookings')); ?></th>
                            <th class="py-3 px-4 font-semibold text-gray-600 dark:text-gray-300 text-center"><?php echo e(__('reports.columns.attendees')); ?></th>
                            <th class="py-3 px-4 font-semibold text-emerald-600 dark:text-emerald-400 text-right"><?php echo e(__('reports.columns.revenue')); ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $report['byTicket']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="py-3 px-4 font-medium text-gray-800 dark:text-gray-200"><?php echo e($row['ticket_type']); ?></td>
                                <td class="py-3 px-4 text-center text-gray-700 dark:text-gray-300"><?php echo e($row['bookings']); ?></td>
                                <td class="py-3 px-4 text-center text-gray-700 dark:text-gray-300"><?php echo e($row['attendees']); ?></td>
                                <td class="py-3 px-4 text-right font-semibold text-emerald-600 dark:text-emerald-400">
                                    <?php echo $__env->make('partials.currency-amount', ['amount' => (float)$row['revenue']], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalee08b1367eba38734199cf7829b1d1e9)): ?>
<?php $attributes = $__attributesOriginalee08b1367eba38734199cf7829b1d1e9; ?>
<?php unset($__attributesOriginalee08b1367eba38734199cf7829b1d1e9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalee08b1367eba38734199cf7829b1d1e9)): ?>
<?php $component = $__componentOriginalee08b1367eba38734199cf7829b1d1e9; ?>
<?php unset($__componentOriginalee08b1367eba38734199cf7829b1d1e9); ?>
<?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $attributes = $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $component = $__componentOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?>
<?php /**PATH /Users/majidalhajri/Downloads/booking/bookingrca/bookingsrca/resources/views/filament/pages/reports.blade.php ENDPATH**/ ?>