<?php
    $attachment = $getState();
    $mailId = is_object($attachment) ? $attachment->mail_id : null;
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mailId && $attachment): ?>
<a type="button"
    href="<?php echo e(route('filament.' . Filament\Facades\Filament::getCurrentPanel()->getId() . '.mails.attachment.download', [
        'tenant' => Filament\Facades\Filament::getTenant(),
        'mail' => $mailId,
        'attachment' => $attachment->id,
        'filename' => $attachment->filename,
    ])); ?>"
    class="rounded-md bg-white px-3.5 py-2.5 text-sm font-semibold cursor-pointer text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Download</a>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\Apache24\htdocs\bookings\vendor\backstage\mails\resources\views\mails\download.blade.php ENDPATH**/ ?>