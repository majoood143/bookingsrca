@php
    use App\Models\CmsSetting;

    $locale = app()->getLocale();

    $title = $model->getTranslation('meta_title', $locale) ?: $model->getTranslation('title', $locale);
    $description = $model->getTranslation('meta_description', $locale)
        ?: $model->getTranslation('excerpt', $locale)
        ?: CmsSetting::get('default_meta_description', '');

    $ogImageUrl = $model->getFirstMediaUrl('og_image') ?: $model->getFirstMediaUrl('featured_image');
    if (!$ogImageUrl && CmsSetting::get('default_og_image')) {
        $ogImageUrl = asset('storage/' . CmsSetting::get('default_og_image'));
    }

    $robots = collect([
        $model->noindex ? 'noindex' : 'index',
        $model->nofollow ? 'nofollow' : 'follow',
    ])->implode(', ');
@endphp

@if ($description)
    <meta name="description" content="{{ $description }}">
@endif
<meta name="robots" content="{{ $robots }}">
@if ($model->canonical_url)
    <link rel="canonical" href="{{ $model->canonical_url }}">
@endif

<meta property="og:type" content="{{ $model->og_type }}">
<meta property="og:title" content="{{ $title }}">
@if ($description)
    <meta property="og:description" content="{{ $description }}">
@endif
<meta property="og:url" content="{{ url()->current() }}">
@if ($ogImageUrl)
    <meta property="og:image" content="{{ $ogImageUrl }}">
@endif

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title }}">
@if ($description)
    <meta name="twitter:description" content="{{ $description }}">
@endif
@if ($ogImageUrl)
    <meta name="twitter:image" content="{{ $ogImageUrl }}">
@endif

<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => $model->og_type === 'article' ? 'Article' : 'WebPage',
    'headline' => $title,
    'description' => $description,
    'url' => url()->current(),
    'inLanguage' => $locale,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
