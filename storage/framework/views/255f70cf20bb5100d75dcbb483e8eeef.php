<div class="space-y-4">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $booking->attendees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attendee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="border rounded-xl p-4 bg-white shadow-sm">
            <div class="flex flex-col md:flex-row justify-between items-start gap-4">
                <div class="flex-1">
                    <h3 class="text-lg font-bold"><?php echo e($attendee->getFullName()); ?></h3>
                    <p class="text-sm text-gray-600"><?php echo e($attendee->email); ?></p>
                    <p class="text-sm text-gray-600">Ticket: <span class="font-mono"><?php echo e($attendee->ticket_number); ?></span></p>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attendee->ticketType): ?>
                        <p class="text-sm text-gray-700 mt-1">
                            <span class="font-semibold">Ticket Type:</span>
                            <?php echo e($attendee->ticketType->getTranslation('name', 'en')); ?>

                            <span class="text-green-600 font-semibold">OMR <?php echo e(number_format($attendee->ticket_price, 3)); ?></span>
                        </p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <div class="mt-2 flex gap-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attendee->email_sent): ?>
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                ✓ Email Sent
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                ✗ Email Not Sent
                            </span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attendee->checked_in): ?>
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                ✓ Checked In
                            </span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                <div class="flex flex-col gap-2 w-full md:w-auto">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attendee->qr_code): ?>
                        <a href="<?php echo e($attendee->getQrCodeUrl()); ?>"
                           target="_blank"
                           class="inline-flex items-center justify-center gap-1.5 px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white shadow-sm transition-colors duration-150"
                           style="background-color: #2563eb;"
                           onmouseover="this.style.backgroundColor='#1d4ed8'"
                           onmouseout="this.style.backgroundColor='#2563eb'">
                            <?php echo e(svg('heroicon-o-qr-code', 'w-4 h-4', ['style' => 'width:16px;height:16px;flex-shrink:0;'])); ?>
                            View QR Code
                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attendee->pdf_path): ?>
                        <a href="<?php echo e($attendee->getPdfUrl()); ?>"
                           download
                           class="inline-flex items-center justify-center gap-1.5 px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white shadow-sm transition-colors duration-150"
                           style="background-color: #16a34a;"
                           onmouseover="this.style.backgroundColor='#15803d'"
                           onmouseout="this.style.backgroundColor='#16a34a'">
                            <?php echo e(svg('heroicon-o-arrow-down-tray', 'w-4 h-4', ['style' => 'width:16px;height:16px;flex-shrink:0;'])); ?>
                            Download Ticket
                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$attendee->email_sent): ?>
                        <button
                            wire:click="sendTicketEmail(<?php echo e($attendee->id); ?>)"
                            type="button"
                            class="inline-flex items-center justify-center gap-1.5 px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white shadow-sm transition-colors duration-150"
                            style="background-color: #7c3aed;"
                            onmouseover="this.style.backgroundColor='#6d28d9'"
                            onmouseout="this.style.backgroundColor='#7c3aed'">
                            <?php echo e(svg('heroicon-o-paper-airplane', 'w-4 h-4', ['style' => 'width:16px;height:16px;flex-shrink:0;'])); ?>
                            Send Ticket
                        </button>
                    <?php else: ?>
                        <button
                            wire:click="resendTicketEmail(<?php echo e($attendee->id); ?>)"
                            type="button"
                            class="inline-flex items-center justify-center gap-1.5 px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 shadow-sm transition-colors duration-150"
                            style="background-color: #ffffff;"
                            onmouseover="this.style.backgroundColor='#f9fafb'"
                            onmouseout="this.style.backgroundColor='#ffffff'">
                            <?php echo e(svg('heroicon-o-arrow-path', 'w-4 h-4', ['style' => 'width:16px;height:16px;flex-shrink:0;'])); ?>
                            Resend Ticket
                        </button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$attendee->checked_in): ?>
                        <button
                            wire:click="checkInAttendee(<?php echo e($attendee->id); ?>)"
                            type="button"
                            class="inline-flex items-center justify-center gap-1.5 px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white shadow-sm transition-colors duration-150"
                            style="background-color: #4f46e5;"
                            onmouseover="this.style.backgroundColor='#4338ca'"
                            onmouseout="this.style.backgroundColor='#4f46e5'">
                            <?php echo e(svg('heroicon-o-check-circle', 'w-4 h-4', ['style' => 'width:16px;height:16px;flex-shrink:0;'])); ?>
                            Check In
                        </button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attendee->qr_code): ?>
                <div class="mt-4 flex flex-col items-center">
                    <div class="inline-block p-3 bg-white border border-gray-200 rounded-lg shadow-sm">
                        <img src="<?php echo e($attendee->getQrCodeBase64()); ?>" alt="QR Code" style="max-width: 160px; width: 100%; height: auto;">
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Scan at entrance to check in</p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\Apache24\htdocs\bookings\resources\views\filament\modals\booking-attendees.blade.php ENDPATH**/ ?>