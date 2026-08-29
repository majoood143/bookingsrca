@php
    $url = $data['url'] ?? null;
    $embedSrc = null;

    if ($url) {
        $host = parse_url($url, PHP_URL_HOST);

        if ($host && preg_match('/(^|\.)(youtube\.com|youtu\.be)$/i', $host)) {
            parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
            $videoId = $query['v'] ?? trim((string) parse_url($url, PHP_URL_PATH), '/');
            $embedSrc = 'https://www.youtube-nocookie.com/embed/'.$videoId;
        } elseif ($host && preg_match('/(^|\.)vimeo\.com$/i', $host)) {
            $videoId = trim((string) parse_url($url, PHP_URL_PATH), '/');
            $embedSrc = 'https://player.vimeo.com/video/'.$videoId;
        }
    }
@endphp

@if ($embedSrc)
    <section class="cms-block px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto aspect-video max-w-(--cms-w,48rem) overflow-hidden rounded-lg bg-black">
            <iframe src="{{ $embedSrc }}" loading="lazy" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"
                class="h-full w-full"></iframe>
        </div>
    </section>
@endif
