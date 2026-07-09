<div
    x-data="{
        timeoutSeconds: <?php echo e((int) $kiosk->idle_timeout_seconds); ?>,
        timer: null,
        ping() {
            clearTimeout(this.timer);
            this.timer = setTimeout(() => { $wire.resetKiosk() }, this.timeoutSeconds * 1000);
        },
    }"
    x-init="ping()"
    @click.window="ping()"
    @touchstart.window="ping()"
    dir="<?php echo e(app()->getLocale() === 'ar' ? 'rtl' : 'ltr'); ?>"
    class="min-h-screen bg-gray-50"
>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$kiosk->is_active): ?>
    
    <div class="min-h-screen flex items-center justify-center px-6 text-center">
        <div>
            <div class="text-7xl mb-6">🛠️</div>
            <h1 class="text-3xl font-bold text-gray-900 mb-3"><?php echo e(__('kiosk_booking.inactive.heading')); ?></h1>
            <p class="text-gray-500 text-lg"><?php echo e(__('kiosk_booking.inactive.body')); ?></p>
        </div>
    </div>
<?php else: ?>

    
    <div class="bg-gradient-to-r from-brand to-brand-hover text-white">
        <div class="max-w-5xl mx-auto px-6 py-6 flex items-center justify-between">
            <img src="<?php echo e(asset('storage/images/horizontalLogo-03.svg')); ?>" alt="Logo" class="h-14 w-auto shrink-0">

            <div class="flex items-center gap-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(app()->getLocale() === 'ar'): ?>
                    <a href="<?php echo e(route('lang.switch', 'en')); ?>"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold bg-white/20 hover:bg-white/30 text-white rounded-full transition-colors">
                        🌐 English
                    </a>
                <?php else: ?>
                    <a href="<?php echo e(route('lang.switch', 'ar')); ?>"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold bg-white/20 hover:bg-white/30 text-white rounded-full transition-colors">
                        🌐 العربية
                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-6 py-10">

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step === 0): ?>
            <h2 class="text-3xl font-bold text-gray-900 mb-1"><?php echo e(__('kiosk_booking.choose_event.heading')); ?></h2>
            <p class="text-gray-500 text-lg mb-8"><?php echo e(__('kiosk_booking.choose_event.subheading')); ?></p>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pickableEvents->isEmpty()): ?>
                <div class="text-center py-16 bg-white rounded-2xl border border-gray-200">
                    <p class="text-gray-400 text-lg"><?php echo e(__('kiosk_booking.choose_event.no_events')); ?></p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $pickableEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <button wire:click="chooseEvent(<?php echo e($ev->id); ?>)"
                            class="p-6 border-2 border-gray-200 bg-white rounded-2xl text-start hover:border-blue-400 hover:shadow-md transition-all">
                            <h3 class="text-xl font-bold text-gray-900">
                                <?php echo e($ev->getTranslation('title', app()->getLocale())); ?></h3>
                            <p class="text-gray-500 mt-1">
                                <?php echo e($ev->getTranslation('location', app()->getLocale())); ?></p>
                        </button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step >= 1 && $step <= 5): ?>
            <?php echo $__env->make('livewire.partials.booking-wizard-steps', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step === 6): ?>
            <div class="max-w-2xl mx-auto">
                <h2 class="text-3xl font-bold text-gray-900 mb-1 text-center"><?php echo e(__('kiosk_booking.step6.heading')); ?></h2>
                <p class="text-gray-500 text-lg mb-8 text-center"><?php echo e(__('kiosk_booking.step6.subheading')); ?></p>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('error')): ?>
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-center">
                        <?php echo e(session('error')); ?>

                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($awaitingCardTap): ?>
                    
                    <div class="p-10 border-2 border-blue-400 bg-blue-50 rounded-3xl text-center">
                        <div class="text-7xl mb-4 animate-pulse">📲</div>
                        <p class="text-xl font-bold text-blue-800"><?php echo e(__('kiosk_booking.step6.wallet_waiting')); ?></p>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(app()->isLocal()): ?>
                            
                            <form wire:submit.prevent="$dispatch('card-tapped', { uid: $refs.devUid.value })"
                                class="mt-6 flex items-center justify-center gap-2">
                                <input x-ref="devUid" type="text" placeholder="Card UID (dev only)"
                                    class="px-4 py-2 border border-gray-300 rounded-xl text-sm">
                                <button type="submit"
                                    class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-xl">Simulate
                                    Tap</button>
                            </form>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <button wire:click="cancelWalletPayment"
                            class="mt-6 inline-flex items-center gap-1.5 text-blue-700 hover:text-blue-900 font-medium text-sm">
                            <?php echo e(__('kiosk_booking.step6.wallet_cancel')); ?>

                        </button>
                    </div>
                <?php else: ?>
                    <div class="space-y-5">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array('wallet', $kiosk->enabled_payment_methods ?? [])): ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($kiosk->reader_connected): ?>
                                <button wire:click="selectWalletPayment"
                                    class="w-full p-6 border-2 border-gray-200 bg-white rounded-2xl flex items-center gap-5 hover:border-blue-400 hover:shadow-md transition-all">
                                    <div class="text-5xl">💳</div>
                                    <div class="text-start">
                                        <p class="text-xl font-bold text-gray-900"><?php echo e(__('kiosk_booking.step6.wallet_title')); ?></p>
                                        <p class="text-gray-500"><?php echo e(__('kiosk_booking.step6.wallet_subtitle')); ?></p>
                                    </div>
                                </button>
                            <?php else: ?>
                                <div class="w-full p-6 border-2 border-gray-100 bg-gray-50 rounded-2xl flex items-center gap-5 opacity-60">
                                    <div class="text-5xl grayscale">💳</div>
                                    <div class="text-start">
                                        <p class="text-xl font-bold text-gray-500"><?php echo e(__('kiosk_booking.step6.wallet_title')); ?></p>
                                        <p class="text-gray-400"><?php echo e(__('kiosk_booking.step6.wallet_unavailable')); ?></p>
                                    </div>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array('pay_at_counter', $kiosk->enabled_payment_methods ?? [])): ?>
                            <button wire:click="payAtCounter"
                                wire:loading.attr="disabled" wire:target="payAtCounter"
                                class="w-full p-6 border-2 border-gray-200 bg-white rounded-2xl flex items-center gap-5 hover:border-blue-400 hover:shadow-md transition-all disabled:opacity-60">
                                <div class="text-5xl">🧾</div>
                                <div class="text-start">
                                    <p class="text-xl font-bold text-gray-900"><?php echo e(__('kiosk_booking.step6.counter_title')); ?></p>
                                    <p class="text-gray-500"><?php echo e(__('kiosk_booking.step6.counter_subtitle')); ?></p>
                                </div>
                            </button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <button wire:click="previousStep"
                        class="mt-8 inline-flex items-center gap-1.5 text-gray-500 hover:text-gray-800 font-medium text-sm">
                        ← <?php echo e(__('event_booking.back')); ?>

                    </button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step === 7 && $confirmedBooking): ?>
            <div class="max-w-xl mx-auto text-center">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($confirmedBooking->payment_status === 'paid'): ?>
                    <div class="text-7xl mb-4">✅</div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-2"><?php echo e(__('kiosk_booking.confirmation.paid_heading')); ?></h2>
                    <p class="text-gray-500 text-lg mb-8"><?php echo e(__('kiosk_booking.confirmation.paid_body')); ?></p>
                <?php else: ?>
                    <div class="text-7xl mb-4">🧾</div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-2"><?php echo e(__('kiosk_booking.confirmation.pending_heading')); ?></h2>
                    <p class="text-gray-500 text-lg mb-8"><?php echo e(__('kiosk_booking.confirmation.pending_body')); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="p-8 bg-white border-2 border-gray-200 rounded-3xl">
                    <p class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-1">
                        <?php echo e(__('kiosk_booking.confirmation.reference')); ?></p>
                    <p class="text-4xl font-black text-gray-900 tracking-wide"><?php echo e($confirmedBooking->booking_reference); ?></p>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($confirmedBooking->payment_status !== 'paid'): ?>
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <p class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-1">
                                <?php echo e(__('kiosk_booking.confirmation.amount_due')); ?></p>
                            <p class="text-2xl font-black text-amber-600">
                                OMR<?php echo e(number_format($confirmedBooking->total_price, 3)); ?></p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <button wire:click="resetKiosk"
                    class="mt-8 px-8 py-3 bg-brand text-white font-bold text-base rounded-xl hover:bg-brand-hover transition-colors shadow-md">
                    <?php echo e(__('kiosk_booking.confirmation.new_booking')); ?>

                </button>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>

    <?php
        $__scriptKey = '3061813367-0';
        ob_start();
    ?>
<script>
    // Native app → web: relay a card UID read from the ACR122U into Livewire.
    window.addEventListener('kiosk:card-tapped', (e) => {
        $wire.dispatch('card-tapped', { uid: e.detail.uid });
    });

    // Web → native app: hand the receipt payload to the kiosk app's printer bridge.
    $wire.on('print-receipt', ({ receipt }) => {
        if (window.KioskPrinter && typeof window.KioskPrinter.print === 'function') {
            window.KioskPrinter.print(JSON.stringify(receipt));
        }
    });
</script>
    <?php
        $__output = ob_get_clean();

        \Livewire\store($this)->push('scripts', $__output, $__scriptKey)
    ?>
<?php /**PATH /Users/majidalhajri/Downloads/booking/bookingrca/bookingsrca/resources/views/livewire/kiosk/kiosk-booking.blade.php ENDPATH**/ ?>