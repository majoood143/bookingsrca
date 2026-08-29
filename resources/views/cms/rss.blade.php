{!! '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' !!}
<rss version="2.0">
    <channel>
        <title>{{ $siteName }}</title>
        <link>{{ url('/blog') }}</link>
        <description>{{ $siteName }} blog feed</description>
        <language>{{ $locale }}</language>
        @foreach ($posts as $post)
            <item>
                <title>{{ $post->getTranslation('title', $locale) }}</title>
                <link>{{ url('/blog/'.$post->slug) }}</link>
                <guid>{{ url('/blog/'.$post->slug) }}</guid>
                <pubDate>{{ ($post->published_at ?? $post->created_at)->toRfc2822String() }}</pubDate>
                <description><![CDATA[{{ $post->getTranslation('excerpt', $locale) }}]]></description>
            </item>
        @endforeach
    </channel>
</rss>
