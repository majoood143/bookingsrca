{{-- Expects: $pageTitle, $description, $canonical, ?$image, ?$jsonLd --}}
@push('meta')
    @if ($description)
        <meta name="description" content="{{ $description }}">
        <meta property="og:description" content="{{ $description }}">
    @endif
    <link rel="canonical" href="{{ $canonical }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:url" content="{{ $canonical }}">
    @if (!empty($image))
        <meta property="og:image" content="{{ $image }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">
    @if (!empty($jsonLd))
        <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endif
@endpush
