@php
    $gtmId = trim((string) \App\Models\BookingSetting::get('google_tag_manager_id', ''));
@endphp
@if (preg_match('/^GTM-[A-Za-z0-9]+$/', $gtmId))
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $gtmId }}" height="0" width="0"
            style="display:none;visibility:hidden"></iframe></noscript>
@endif
