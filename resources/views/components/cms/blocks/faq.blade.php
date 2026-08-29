@php $locale = app()->getLocale(); @endphp

<section class="cms-block px-4 py-12 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-(--cms-w,48rem) divide-y divide-gray-200" x-data="{ open: null }">
        @foreach ($data['items'] ?? [] as $i => $item)
            <div class="py-4">
                <button type="button" @click="open = open === {{ $i }} ? null : {{ $i }}"
                    class="flex w-full items-center justify-between text-left text-lg font-medium text-gray-900">
                    <span>{{ $item['question'][$locale] ?? $item['question']['en'] ?? '' }}</span>
                    <span class="ms-4 shrink-0 text-gray-400" x-text="open === {{ $i }} ? '−' : '+'"></span>
                </button>
                <div x-show="open === {{ $i }}" x-collapse>
                    <p class="mt-3 text-gray-600">{{ $item['answer'][$locale] ?? $item['answer']['en'] ?? '' }}</p>
                </div>
            </div>
        @endforeach
    </div>
</section>
