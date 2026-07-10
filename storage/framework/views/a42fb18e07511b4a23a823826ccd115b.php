<?php $__env->startPush('meta'); ?>
    <meta name="robots" content="noindex, nofollow">
<?php $__env->stopPush(); ?>
<div dir="<?php echo e(app()->getLocale() === 'ar' ? 'rtl' : 'ltr'); ?>">
    <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
        <div class="max-w-md w-full text-center py-12">
            <div class="flex justify-end mb-6">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(app()->getLocale() === 'ar'): ?>
                    <a href="<?php echo e(route('lang.switch', 'en')); ?>"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-full transition-colors">
                        🌐 <?php echo e(__('event_booking.switch_to_english')); ?>

                    </a>
                <?php else: ?>
                    <a href="<?php echo e(route('lang.switch', 'ar')); ?>"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-full transition-colors">
                        🌐 <?php echo e(__('event_booking.switch_to_arabic')); ?>

                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <img src="<?php echo e(asset('storage/images/horizontalLogo-03.svg')); ?>" alt="Logo"
                class="h-20 w-auto mx-auto mb-8">

            <div class="text-6xl mb-4">🔒</div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3">
                <?php echo e(__('event_booking.private.heading')); ?>

            </h1>
            <p class="text-gray-500 text-base leading-relaxed mb-6">
                <?php echo e(__('event_booking.private.message')); ?>

            </p>

            <form wire:submit.prevent="submitEventPassword" class="text-start">
                <label for="eventPasswordInput" class="sr-only">
                    <?php echo e(__('event_booking.private.password_label')); ?>

                </label>
                <input
                    type="password"
                    id="eventPasswordInput"
                    wire:model="eventPasswordInput"
                    placeholder="<?php echo e(__('event_booking.private.password_placeholder')); ?>"
                    autofocus
                    class="w-full rounded-xl border-gray-300 focus:border-brand focus:ring-brand shadow-sm px-4 py-3"
                >
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($eventPasswordError): ?>
                    <p class="mt-2 text-sm text-red-600"><?php echo e($eventPasswordError); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <button type="submit"
                    class="mt-4 w-full inline-flex items-center justify-center gap-1.5 px-6 py-3 bg-brand text-white font-semibold rounded-xl hover:bg-brand-hover transition-colors shadow-sm">
                    <?php echo e(__('event_booking.private.submit')); ?>

                </button>
            </form>
        </div>
    </div>
</div>
<?php /**PATH /Users/majidalhajri/Downloads/booking/bookingrca/bookingsrca/resources/views/livewire/event-password-prompt.blade.php ENDPATH**/ ?>