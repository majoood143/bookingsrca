<?php
    use App\Models\BookingSetting;

    $primaryColor = BookingSetting::get('primary_color', '#05602b');
    $secondaryColor = BookingSetting::get('secondary_color', '#0da74c');
?>
<div dir="<?php echo e(app()->getLocale() === 'ar' ? 'rtl' : 'ltr'); ?>"
    style="--color-brand: <?php echo e($primaryColor); ?>; --color-brand-hover: <?php echo e($secondaryColor); ?>;">
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($event->status, ['draft', 'cancelled'])): ?>
    
    <div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
        <div class="max-w-lg w-full text-center py-12">
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

            <div class="text-6xl mb-4">🛠️</div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3">
                <?php echo e(__('event_booking.unavailable.heading')); ?>

            </h1>
            <p class="text-gray-500 text-base leading-relaxed">
                <?php echo e($event->status === 'cancelled'
                    ? __('event_booking.unavailable.cancelled_message')
                    : __('event_booking.unavailable.draft_message')); ?>

            </p>

            <a href="<?php echo e(url('/')); ?>"
                class="mt-8 inline-flex items-center gap-1.5 px-6 py-2.5 bg-brand text-white font-semibold rounded-xl hover:bg-brand-hover transition-colors shadow-sm">
                <?php echo e(__('event_booking.unavailable.back_home')); ?>

            </a>
        </div>
    </div>
<?php elseif($passwordRequired): ?>
    <?php $__env->startPush('meta'); ?>
        <meta name="robots" content="noindex, nofollow">
    <?php $__env->stopPush(); ?>
    
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
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['eventPasswordInput'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="mt-2 text-sm text-red-600"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($eventPasswordError): ?>
                    <p class="mt-2 text-sm text-red-600"><?php echo e($eventPasswordError); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <button type="submit"
                    class="mt-4 w-full inline-flex items-center justify-center gap-1.5 px-6 py-3 bg-brand text-white font-semibold rounded-xl hover:bg-brand-hover transition-colors shadow-sm">
                    <?php echo e(__('event_booking.private.submit')); ?>

                </button>
            </form>

            <a href="<?php echo e(url('/')); ?>"
                class="mt-6 inline-block text-sm text-gray-400 hover:text-gray-600 transition-colors">
                <?php echo e(__('event_booking.unavailable.back_home')); ?>

            </a>
        </div>
    </div>
<?php else: ?>
    <div class="min-h-screen bg-gray-50">

    
    <div class="bg-gradient-to-r from-brand to-brand-hover text-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-8 sm:py-10">
            
            <div class="flex justify-end mb-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(app()->getLocale() === 'ar'): ?>
                    <a href="<?php echo e(route('lang.switch', 'en')); ?>"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-white/20 hover:bg-white/30 text-white rounded-full transition-colors">
                        🌐 <?php echo e(__('event_booking.switch_to_english')); ?>

                    </a>
                <?php else: ?>
                    <a href="<?php echo e(route('lang.switch', 'ar')); ?>"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold bg-white/20 hover:bg-white/30 text-white rounded-full transition-colors">
                        🌐 <?php echo e(__('event_booking.switch_to_arabic')); ?>

                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="flex items-center justify-between gap-4">
                <h1 class="text-2xl sm:text-3xl font-bold leading-tight">
                    <?php echo e($event->getTranslation('title', app()->getLocale())); ?>

                </h1>
                <img src="<?php echo e(asset('storage/images/horizontalLogo-03.svg')); ?>" alt="Logo"
                    class="h-40 sm:h-40 w-auto shrink-0">
            </div>
            <p class="mt-2 text-blue-100 text-base sm:text-lg line-clamp-4">
                <?php echo e($event->getTranslation('description', app()->getLocale())); ?>

            </p>
            <div class="mt-3 flex flex-wrap gap-x-5 gap-y-1 text-sm text-blue-200">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($event->location_link): ?>
                    <a href="<?php echo e($event->location_link); ?>" target="_blank" rel="noopener noreferrer"
                        class="hover:text-white underline-offset-2 hover:underline">
                        📍 <?php echo e($event->getTranslation('location', app()->getLocale())); ?>

                    </a>
                <?php else: ?>
                    <span>📍 <?php echo e($event->getTranslation('location', app()->getLocale())); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($event->organizer): ?>
                    <span>👤 <?php echo e($event->organizer); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($event->organizer_phone): ?>
                    <a href="tel:<?php echo e($event->organizer_phone); ?>" class="hover:text-white">
                        📞 <?php echo e($event->organizer_phone); ?>

                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>

    
    <div class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-20">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-4">
            <div class="flex items-center">
                <?php
                    $steps = [
                        1 => __('event_booking.steps.date'),
                        2 => __('event_booking.steps.time'),
                        3 => __('event_booking.steps.tickets'),
                    ];
                    if ($extraServices->isNotEmpty()) {
                        $steps[4] = __('event_booking.steps.extras');
                    }
                    $steps[5] = __('event_booking.steps.details');
                    $steps[6] = __('event_booking.steps.payment');
                ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $num => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-center <?php echo e($num < 6 ? 'flex-1' : ''); ?>">
                        <div class="flex items-center gap-2 shrink-0">
                            <div
                                class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-colors
                                <?php echo e($step > $num
                                    ? 'bg-green-500 text-white'
                                    : ($step === $num
                                        ? 'bg-blue-600 text-white ring-4 ring-blue-100'
                                        : 'bg-gray-200 text-gray-500')); ?>">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step > $num): ?>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                <?php else: ?>
                                    <?php echo e($num); ?>

                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <span
                                class="hidden sm:block text-sm font-medium
                                <?php echo e($step >= $num ? 'text-gray-800' : 'text-gray-400'); ?>">
                                <?php echo e($label); ?>

                            </span>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($num < 6): ?>
                            <div
                                class="flex-1 h-0.5 mx-2 sm:mx-3 transition-colors
                                <?php echo e($step > $num ? 'bg-green-400' : 'bg-gray-200'); ?>">
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>

    
    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-8">

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step === 1): ?>
            <?php
                $eventTimeline = $event->getTranslation('timeline', app()->getLocale());
                $eventFaq      = $event->faq ?? [];
                $videoEmbedId  = $event->getPromotionalVideoEmbedId();
                $hasTimeline   = filled(trim(strip_tags($eventTimeline ?? '')));
            ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($videoEmbedId || $hasTimeline || !empty($eventFaq)): ?>
                <div class="mb-8 space-y-6">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($videoEmbedId): ?>
                        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                            <div class="aspect-video">
                                <iframe class="w-full h-full"
                                    src="https://www.youtube.com/embed/<?php echo e($videoEmbedId); ?>"
                                    title="<?php echo e(__('event_booking.details.promo_video')); ?>"
                                    frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                    allowfullscreen></iframe>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasTimeline): ?>
                        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 sm:p-6">
                            <h2 class="text-lg font-bold text-gray-900 mb-3"><?php echo e(__('event_booking.details.timeline')); ?></h2>
                            <div class="prose prose-sm max-w-none text-gray-600 leading-relaxed">
                                <?php echo $eventTimeline; ?>

                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($eventFaq)): ?>
                        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 sm:p-6">
                            <h2 class="text-lg font-bold text-gray-900 mb-3"><?php echo e(__('event_booking.details.faq')); ?></h2>
                            <div class="divide-y divide-gray-100">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $eventFaq; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $question = $item['question'][app()->getLocale()] ?? $item['question']['en'] ?? '';
                                        $answer   = $item['answer'][app()->getLocale()] ?? $item['answer']['en'] ?? '';
                                    ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($question): ?>
                                        <details class="group py-3">
                                            <summary
                                                class="flex items-center justify-between cursor-pointer list-none font-semibold text-gray-800 text-sm">
                                                <?php echo e($question); ?>

                                                <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 group-open:rotate-180 shrink-0 ms-3"
                                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                    fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </summary>
                                            <p class="mt-2 text-sm text-gray-500 leading-relaxed"><?php echo e($answer); ?></p>
                                        </details>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php echo $__env->make('livewire.partials.booking-wizard-steps', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step === 6): ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                
                <div class="lg:col-span-2">
                    <h2 class="text-2xl font-bold text-gray-900 mb-1"><?php echo e(__('event_booking.step6.heading')); ?></h2>
                    <p class="text-gray-500 mb-6"><?php echo e(__('event_booking.step6.subheading')); ?></p>

                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeGateway === 'thawani'): ?>
                        <div
                            class="p-6 border-2 border-blue-500 bg-blue-50 rounded-2xl shadow-md shadow-blue-100 mb-6">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-sm border border-blue-100 shrink-0">
                                    
                                    <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900 text-lg">
                                        <?php echo e(__('event_booking.step6.thawani_title')); ?></p>
                                    <p class="text-sm text-gray-500"><?php echo e(__('event_booking.step6.thawani_subtitle')); ?>

                                    </p>
                                </div>
                                <div class="ml-auto">
                                    <span
                                        class="inline-block w-3 h-3 rounded-full bg-blue-500 ring-4 ring-blue-200"></span>
                                </div>
                            </div>
                            <p class="mt-4 text-sm text-blue-700 bg-blue-100 rounded-lg px-3 py-2">
                                <?php echo e(__('event_booking.step6.thawani_redirect_note')); ?>

                            </p>
                        </div>
                    <?php elseif($activeGateway === 'nbo'): ?>
                        <div
                            class="p-6 border-2 border-indigo-500 bg-indigo-50 rounded-2xl shadow-md shadow-indigo-100 mb-6">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-sm border border-indigo-100 shrink-0">
                                    <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900 text-lg">
                                        <?php echo e(__('event_booking.step6.nbo_title')); ?></p>
                                    <p class="text-sm text-gray-500"><?php echo e(__('event_booking.step6.nbo_subtitle')); ?></p>
                                </div>
                                <div class="ml-auto">
                                    <span
                                        class="inline-block w-3 h-3 rounded-full bg-indigo-500 ring-4 ring-indigo-200"></span>
                                </div>
                            </div>
                            <p class="mt-4 text-sm text-indigo-700 bg-indigo-100 rounded-lg px-3 py-2">
                                <?php echo e(__('event_booking.step6.nbo_redirect_note')); ?>

                            </p>
                        </div>
                    <?php elseif($activeGateway === 'ccavenue'): ?>
                        <div
                            class="p-6 border-2 border-teal-500 bg-teal-50 rounded-2xl shadow-md shadow-teal-100 mb-6">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-sm border border-teal-100 shrink-0">
                                    <svg class="w-7 h-7 text-teal-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900 text-lg">
                                        <?php echo e(__('event_booking.step6.ccavenue_title')); ?></p>
                                    <p class="text-sm text-gray-500"><?php echo e(__('event_booking.step6.ccavenue_subtitle')); ?>

                                    </p>
                                </div>
                                <div class="ml-auto">
                                    <span
                                        class="inline-block w-3 h-3 rounded-full bg-teal-500 ring-4 ring-teal-200"></span>
                                </div>
                            </div>
                            <p class="mt-4 text-sm text-teal-700 bg-teal-100 rounded-lg px-3 py-2">
                                <?php echo e(__('event_booking.step6.ccavenue_redirect_note')); ?>

                            </p>
                        </div>
                    <?php elseif($activeGateway === 'cash'): ?>
                        <div class="p-6 border-2 border-amber-400 bg-amber-50 rounded-2xl mb-6">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-sm border border-amber-100 shrink-0">
                                    <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900 text-lg">
                                        <?php echo e(__('event_booking.step6.pay_at_door_title')); ?></p>
                                    <p class="text-sm text-gray-500">
                                        <?php echo e(__('event_booking.step6.pay_at_door_subtitle')); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        
                        <div class="p-6 border-2 border-green-400 bg-green-50 rounded-2xl mb-6">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-sm border border-green-100 shrink-0">
                                    <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900 text-lg">
                                        <?php echo e(__('event_booking.step6.free_title')); ?></p>
                                    <p class="text-sm text-gray-500"><?php echo e(__('event_booking.step6.free_subtitle')); ?>

                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <div class="flex items-start gap-3">
                        <input type="checkbox" wire:model="agreedToTerms" id="agreedToTerms1"
                            class="mt-0.5 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer shrink-0
                                        <?php echo e($errors->has('agreedToTerms') ? 'border-red-400 ring-1 ring-red-300' : ''); ?>">
                        <label for="agreedToTerms" class="text-sm text-gray-700 cursor-pointer leading-snug">
                            <?php echo e(__('event_booking.step5.terms_agree')); ?>

                            <span
                                class="font-semibold text-gray-900"><?php echo e(__('event_booking.step5.terms_heading')); ?></span>
                            <span class="text-red-500 ml-0.5">*</span>
                        </label>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['agreedToTerms1'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-red-500 text-sm -mt-3"><?php echo e(__('event_booking.step5.terms_required')); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <button type="button" wire:click="previousStep"
                            class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-gray-600 hover:text-gray-900 font-medium text-sm transition-colors border border-gray-200 rounded-xl hover:bg-gray-50 sm:w-auto">
                            ← <?php echo e(__('event_booking.back')); ?>

                        </button>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeGateway === 'thawani'): ?>
                            <button type="button" wire:click="submitBooking"
                                class="flex-1 sm:flex-none px-8 py-3 bg-blue-600 text-white font-bold text-base rounded-xl hover:bg-blue-700 transition-colors shadow-md shadow-blue-200 disabled:opacity-60 disabled:cursor-not-allowed"
                                wire:loading.attr="disabled" wire:target="submitBooking">
                                <span wire:loading.remove wire:target="submitBooking">
                                    🔒 <?php echo e(__('event_booking.step6.pay_thawani_btn')); ?>

                                </span>
                                <span wire:loading wire:target="submitBooking" class="inline-flex items-center gap-2">
                                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                    </svg>
                                    <?php echo e(__('event_booking.step6.redirecting_thawani')); ?>

                                </span>
                            </button>
                        <?php elseif($activeGateway === 'nbo'): ?>
                            <button type="button" wire:click="submitBooking"
                                class="flex-1 sm:flex-none px-8 py-3 bg-indigo-600 text-white font-bold text-base rounded-xl hover:bg-indigo-700 transition-colors shadow-md shadow-indigo-200 disabled:opacity-60 disabled:cursor-not-allowed"
                                wire:loading.attr="disabled" wire:target="submitBooking">
                                <span wire:loading.remove wire:target="submitBooking">
                                    🔒 <?php echo e(__('event_booking.step6.pay_nbo_btn')); ?>

                                </span>
                                <span wire:loading wire:target="submitBooking" class="inline-flex items-center gap-2">
                                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                    </svg>
                                    <?php echo e(__('event_booking.step6.redirecting_nbo')); ?>

                                </span>
                            </button>
                        <?php elseif($activeGateway === 'ccavenue'): ?>
                            <button type="button" wire:click="submitBooking"
                                class="flex-1 sm:flex-none px-8 py-3 bg-teal-600 text-white font-bold text-base rounded-xl hover:bg-teal-700 transition-colors shadow-md shadow-teal-200 disabled:opacity-60 disabled:cursor-not-allowed"
                                wire:loading.attr="disabled" wire:target="submitBooking">
                                <span wire:loading.remove wire:target="submitBooking">
                                    🔒 <?php echo e(__('event_booking.step6.pay_ccavenue_btn')); ?>

                                </span>
                                <span wire:loading wire:target="submitBooking" class="inline-flex items-center gap-2">
                                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                    </svg>
                                    <?php echo e(__('event_booking.step6.redirecting_ccavenue')); ?>

                                </span>
                            </button>
                        <?php else: ?>
                            <button type="button" wire:click="submitBooking"
                                class="flex-1 sm:flex-none px-8 py-3 bg-green-600 text-white font-bold text-base rounded-xl hover:bg-green-700 transition-colors shadow-md shadow-green-200 disabled:opacity-60 disabled:cursor-not-allowed"
                                wire:loading.attr="disabled" wire:target="submitBooking">
                                <span wire:loading.remove wire:target="submitBooking">
                                    ✓ <?php echo e(__('event_booking.step6.confirm_booking')); ?>

                                </span>
                                <span wire:loading wire:target="submitBooking" class="inline-flex items-center gap-2">
                                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                    </svg>
                                    <?php echo e(__('event_booking.step6.processing')); ?>

                                </span>
                            </button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                
                <div class="lg:col-span-1">
                    <div
                        class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden lg:sticky lg:top-24">
                        <div class="bg-gradient-to-r from-brand to-brand-hover text-white px-5 py-4">
                            <h3 class="font-bold text-base"><?php echo e(__('event_booking.step6.order_summary')); ?></h3>
                        </div>
                        <div class="p-5 space-y-4 text-sm">

                            <div>
                                <p class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-0.5">
                                    <?php echo e(__('event_booking.summary.event')); ?></p>
                                <p class="font-semibold text-gray-900 leading-tight">
                                    <?php echo e($event->getTranslation('title', app()->getLocale())); ?></p>
                            </div>

                            <div class="pt-3 border-t border-gray-100 grid grid-cols-2 gap-3">
                                <div>
                                    <p class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-0.5">
                                        <?php echo e(__('event_booking.summary.date')); ?></p>
                                    <p class="font-medium text-gray-800">
                                        <?php echo e(\Carbon\Carbon::parse($selectedDate)->locale(app()->getLocale())->translatedFormat('M d, Y')); ?>

                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-0.5">
                                        <?php echo e(__('event_booking.summary.time')); ?></p>
                                    <p class="font-medium text-gray-800">
                                        <?php echo e($showSlotEndTime ? $timeSlots->find($selectedSlot)?->getTimeRange() : $timeSlots->find($selectedSlot)?->start_time->format('H:i')); ?></p>
                                </div>
                            </div>

                            
                            <div class="pt-3 border-t border-gray-100">
                                <p class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-2">
                                    <?php echo e(__('event_booking.summary.tickets')); ?></p>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $ticketTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticketType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php $qty = $ticketQuantities[$ticketType->id] ?? 0; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($qty > 0): ?>
                                        <div class="flex justify-between text-gray-600 text-xs mb-1">
                                            <span
                                                class="truncate pr-2"><?php echo e($ticketType->getTranslation('name', app()->getLocale())); ?>

                                                × <?php echo e($qty); ?></span>
                                            <span
                                                class="shrink-0"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ticketType->price > 0): ?><?php echo $__env->make('partials.currency-amount', ['amount' => $ticketType->price * $qty], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php else: ?><?php echo e(__('event_booking.step3.free')); ?><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></span>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($attendees)): ?>
                                <div class="pt-3 border-t border-gray-100">
                                    <p class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-2">
                                        <?php echo e(__('event_booking.summary.attendees')); ?></p>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $attendees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attendee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php $attendeeTicketType = $ticketTypes->find($attendee['ticket_type_id'] ?? null); ?>
                                        <div class="flex justify-between items-center text-gray-700 mb-1">
                                            <span
                                                class="truncate pr-2"><?php echo e(trim(($attendee['first_name'] ?? '') . ' ' . ($attendee['last_name'] ?? '')) ?: '—'); ?></span>
                                            <span
                                                class="shrink-0 text-gray-400 text-xs truncate max-w-[40%]"><?php echo e($attendeeTicketType?->getTranslation('name', app()->getLocale())); ?></span>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <?php
                                $hasServices = collect($ticketTypeServices)->flatten()->sum() > 0;
                            ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasServices): ?>
                                <div class="pt-3 border-t border-gray-100">
                                    <p class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-2">
                                        <?php echo e(__('event_booking.summary.extra_services')); ?></p>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $ticketTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticketType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $qty = $ticketQuantities[$ticketType->id] ?? 0;
                                            $serviceCounts = $ticketTypeServices[$ticketType->id] ?? [];
                                        ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($qty > 0 && !empty($serviceCounts)): ?>
                                            <p class="text-xs text-gray-400 mb-1">
                                                <?php echo e($ticketType->getTranslation('name', app()->getLocale())); ?></p>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $serviceCounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $serviceId => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php $svc = $count > 0 ? $extraServices->find($serviceId) : null; ?>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($svc): ?>
                                                    <div class="flex justify-between text-gray-600 text-xs mb-1">
                                                        <span
                                                            class="truncate pr-2"><?php echo e($svc->getTranslation('name', app()->getLocale())); ?>

                                                            × <?php echo e($count); ?></span>
                                                        <span
                                                            class="shrink-0"><?php echo $__env->make('partials.currency-amount', ['amount' => $svc->price * $count], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></span>
                                                    </div>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            
                            <div class="pt-3 border-t border-gray-100">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($promoApplied): ?>
                                    <div class="flex items-center justify-between bg-green-50 border border-green-200 rounded-lg px-3 py-2">
                                        <div class="text-xs">
                                            <p class="font-semibold text-green-700"><?php echo e(__('promo.applied')); ?> — <?php echo e(strtoupper($promoCode)); ?></p>
                                        </div>
                                        <button type="button" wire:click="removePromoCode"
                                            class="text-xs text-gray-400 hover:text-red-500 font-medium">
                                            <?php echo e(__('promo.remove')); ?>

                                        </button>
                                    </div>
                                <?php else: ?>
                                    <div class="flex gap-2">
                                        <input type="text" wire:model="promoCode"
                                            wire:keydown.enter.prevent="applyPromoCode"
                                            placeholder="<?php echo e(__('promo.placeholder')); ?>"
                                            class="flex-1 min-w-0 rounded-lg border-gray-300 text-sm uppercase focus:border-brand focus:ring-brand">
                                        <button type="button" wire:click="applyPromoCode" wire:loading.attr="disabled"
                                            wire:target="applyPromoCode"
                                            class="shrink-0 px-3 py-1.5 rounded-lg bg-gray-900 text-white text-xs font-semibold hover:bg-gray-700 disabled:opacity-50">
                                            <span wire:loading.remove wire:target="applyPromoCode"><?php echo e(__('promo.apply')); ?></span>
                                            <span wire:loading wire:target="applyPromoCode"><?php echo e(__('promo.checking')); ?></span>
                                        </button>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($promoMessage): ?>
                                    <p class="text-xs mt-1.5 <?php echo e($promoApplied ? 'text-green-600' : 'text-red-500'); ?>">
                                        <?php echo e($promoMessage); ?></p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($promoApplied && $discountAmount > 0): ?>
                                <div class="pt-3 border-t border-gray-100 space-y-1">
                                    <div class="flex justify-between text-sm text-gray-600">
                                        <span><?php echo e(__('promo.subtotal_label')); ?></span>
                                        <span><?php echo $__env->make('partials.currency-amount', ['amount' => $totalPrice], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></span>
                                    </div>
                                    <div class="flex justify-between text-sm text-green-600 font-medium">
                                        <span><?php echo e(__('promo.discount_label')); ?></span>
                                        <span>-<?php echo $__env->make('partials.currency-amount', ['amount' => $discountAmount], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></span>
                                    </div>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <div class="pt-3 border-t-2 border-gray-200 flex justify-between items-baseline">
                                <span
                                    class="font-bold text-gray-900 text-base"><?php echo e(__('event_booking.summary.total')); ?></span>
                                <span
                                    class="font-black text-2xl text-blue-600"><?php echo $__env->make('partials.currency-amount', ['amount' => $this->finalTotal()], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></span>
                            </div>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeGateway === 'thawani'): ?>
                                <div class="pt-2 text-center text-xs text-gray-400">
                                    <svg class="inline w-4 h-4 mr-1 text-green-500" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <?php echo e(__('event_booking.step6.secured_by_thawani')); ?>

                                </div>
                            <?php elseif($activeGateway === 'nbo'): ?>
                                <div class="pt-2 text-center text-xs text-gray-400">
                                    <svg class="inline w-4 h-4 mr-1 text-green-500" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <?php echo e(__('event_booking.step6.secured_by_nbo')); ?>

                                </div>
                            <?php elseif($activeGateway === 'ccavenue'): ?>
                                <div class="pt-2 text-center text-xs text-gray-400">
                                    <svg class="inline w-4 h-4 mr-1 text-green-500" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <?php echo e(__('event_booking.step6.secured_by_ccavenue')); ?>

                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        </div>
                    </div>
                </div>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    </div>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /Users/majidalhajri/Downloads/booking/bookingrca/bookingsrca/resources/views/livewire/event-booking.blade.php ENDPATH**/ ?>