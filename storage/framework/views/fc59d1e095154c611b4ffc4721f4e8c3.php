<?php
    $logoPath = \App\Models\BookingSetting::get('app_logo');
?>
<img
    src="<?php echo e($logoPath ? \Illuminate\Support\Facades\Storage::disk('public')->url($logoPath) : asset('storage/images/horizontalLogo-02.svg')); ?>"
    alt="<?php echo e(\App\Models\BookingSetting::get('site_name_en', 'Bookings')); ?>"
    class="h-20"
>
<?php /**PATH /Users/majidalhajri/Downloads/booking/bookingrca/bookingsrca/resources/views/filament/admin/logo.blade.php ENDPATH**/ ?>