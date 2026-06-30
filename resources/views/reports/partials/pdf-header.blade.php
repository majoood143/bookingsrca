@php
    $isRtl = ($locale ?? 'en') === 'ar';
    $appName = config('app.name');
@endphp
<!DOCTYPE html>
<html dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
@include('reports.partials.pdf-fonts')
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'Almarai', 'Segoe UI', Tahoma, Arial, sans-serif;
        width: 100%;
        text-align: center;
        color: #1f2937;
    }
    .logo {
        height: 46px;
        margin-bottom: 4px;
    }
    .org-name {
        font-size: 11px;
        font-weight: 600;
        color: #374151;
    }
    .doc-title {
        font-size: 14px;
        font-weight: 700;
        color: #065f46;
        margin-top: 2px;
    }
    .period {
        font-size: 9px;
        color: #6b7280;
        margin-top: 2px;
    }
    .timestamp {
        font-size: 8px;
        color: #9ca3af;
        margin-top: 1px;
    }
    .rule {
        margin-top: 6px;
        border-top: 2px solid #065f46;
        width: 100%;
    }
</style>
</head>
<body>
    <img class="logo" src="data:image/jpeg;base64,{{ $logoBase64 ?? '' }}" alt="logo">
    <div class="org-name">{{ $appName }}</div>
    <div class="doc-title">{{ $title }}</div>
    <div class="period">{{ $periodLabel }}</div>
    <div class="timestamp">{{ $isRtl ? 'تاريخ الإنشاء' : 'Generated on' }}: {{ now()->format('Y-m-d H:i') }}</div>
    <div class="rule"></div>
</body>
</html>
