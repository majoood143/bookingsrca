@php
    $almaraiRegular = base64_encode(file_get_contents(public_path('fonts/almarai/Almarai-Regular.ttf')));
    $almaraiBold = base64_encode(file_get_contents(public_path('fonts/almarai/Almarai-Bold.ttf')));
@endphp
<style>
    @font-face {
        font-family: 'Almarai';
        src: url(data:font/ttf;base64,{{ $almaraiRegular }}) format('truetype');
        font-weight: 400;
        font-style: normal;
    }

    @font-face {
        font-family: 'Almarai';
        src: url(data:font/ttf;base64,{{ $almaraiBold }}) format('truetype');
        font-weight: 700;
        font-style: normal;
    }
</style>
