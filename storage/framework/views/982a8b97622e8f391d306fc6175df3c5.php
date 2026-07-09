<?php
    $almaraiRegular = base64_encode(file_get_contents(public_path('fonts/almarai/Almarai-Regular.ttf')));
    $almaraiBold = base64_encode(file_get_contents(public_path('fonts/almarai/Almarai-Bold.ttf')));
?>
<style>
    @font-face {
        font-family: 'Almarai';
        src: url(data:font/ttf;base64,<?php echo e($almaraiRegular); ?>) format('truetype');
        font-weight: 400;
        font-style: normal;
    }

    @font-face {
        font-family: 'Almarai';
        src: url(data:font/ttf;base64,<?php echo e($almaraiBold); ?>) format('truetype');
        font-weight: 700;
        font-style: normal;
    }
</style>
<?php /**PATH /Users/majidalhajri/Downloads/booking/bookingrca/bookingsrca/resources/views/reports/partials/pdf-fonts.blade.php ENDPATH**/ ?>