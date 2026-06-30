<div class="w-full h-screen">
    <iframe
        src="<?php echo e(route('filament.' . Filament\Facades\Filament::getCurrentPanel()->getId() . '.mails.preview', ['tenant' => Filament\Facades\Filament::getTenant(), 'mail' => $mail->id])); ?>"
        class="w-full h-full max-w-full" style="width: 100vw; height: 100vh; border: none;">
    </iframe>
</div>
<?php /**PATH C:\Apache24\htdocs\bookings\vendor\backstage\mails\resources\views\mails\preview.blade.php ENDPATH**/ ?>