<div>
    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('booking-attendees-modal', ['booking' => $booking]);

$__key = 'attendees-' . $booking->id;

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-2011846104-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key);

echo $__html;

unset($__html);
unset($__key);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
</div><?php /**PATH C:\Apache24\htdocs\bookings\resources\views\filament\modals\booking-attendees-wrapper.blade.php ENDPATH**/ ?>