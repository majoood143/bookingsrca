@php
    /** @var \App\Models\GameCampaign $campaign */
    $metaTitle = $title ?? $campaign->translated('name');
    $metaDescription = \Illuminate\Support\Str::limit(strip_tags($campaign->translated('tagline') ?: $campaign->translated('description')), 160);
@endphp
<meta charset="utf-8">
@include('partials.gtm-head')
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="#08120d">
<title>{{ $metaTitle }} · {{ $brand['site_name'] }}</title>
<meta name="description" content="{{ $metaDescription }}">
<meta property="og:type" content="website">
<meta property="og:title" content="{{ $metaTitle }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:url" content="{{ $canonical ?? url()->current() }}">
<meta property="og:locale" content="{{ app()->getLocale() === 'ar' ? 'ar_OM' : 'en_US' }}">
@if ($brand['cover'])
    <meta property="og:image" content="{{ $brand['cover'] }}">
    <meta name="twitter:card" content="summary_large_image">
@endif
@if ($campaign->status !== \App\Models\GameCampaign::STATUS_PUBLISHED)
    <meta name="robots" content="noindex, nofollow">
@endif
<link rel="icon" href="{{ $brand['favicon'] }}">
<style>
    :root {
        --brand: {{ $brand['primary'] }};
        --brand-hi: {{ $brand['secondary'] }};
        --accent: {{ $brand['accent'] }};
    }
</style>
@vite(['resources/css/games.css', $entry])
