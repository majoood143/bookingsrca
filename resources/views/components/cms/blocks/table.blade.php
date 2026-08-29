@php
    $locale = app()->getLocale();
    $title = $data['title'][$locale] ?? $data['title']['en'] ?? '';
    $rows = $data['rows'] ?? [];
    $hasHeader = $data['has_header'] ?? true;
@endphp

<section class="cms-block px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-(--cms-w,64rem)">
        @if ($title)
            <h3 class="mb-4 text-xl font-semibold text-gray-900">{{ $title }}</h3>
        @endif
        <div class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200 text-start text-sm">
                @foreach ($rows as $i => $row)
                    <tr class="{{ $i === 0 && $hasHeader ? 'bg-gray-50' : ($i % 2 === 0 ? 'bg-white' : 'bg-gray-50/50') }}">
                        @foreach ($row['cells'] ?? [] as $cell)
                            @if ($i === 0 && $hasHeader)
                                <th class="px-4 py-3 text-start font-semibold text-gray-900">{{ $cell['value'] ?? '' }}</th>
                            @else
                                <td class="px-4 py-3 text-gray-700">{{ $cell['value'] ?? '' }}</td>
                            @endif
                        @endforeach
                    </tr>
                @endforeach
            </table>
        </div>
    </div>
</section>
