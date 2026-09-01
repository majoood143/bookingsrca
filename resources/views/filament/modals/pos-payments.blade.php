<div class="overflow-x-auto">
    <table class="w-full text-sm text-start">
        <thead>
            <tr class="border-b border-gray-200 dark:border-gray-700 text-xs uppercase text-gray-500">
                <th class="py-2 px-3 text-start">{{ __('Method') }}</th>
                <th class="py-2 px-3 text-start">{{ __('Amount') }}</th>
                <th class="py-2 px-3 text-start">{{ __('Reference') }}</th>
                <th class="py-2 px-3 text-start">{{ __('Date') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($payments as $payment)
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <td class="py-2 px-3">
                        <span class="px-2 py-0.5 rounded text-xs font-semibold uppercase bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                            {{ $payment->method }}
                        </span>
                    </td>
                    <td class="py-2 px-3">{{ number_format((float) $payment->amount, 3) }}</td>
                    <td class="py-2 px-3">{{ $payment->reference ?? '—' }}</td>
                    <td class="py-2 px-3 text-gray-500">{{ $payment->created_at?->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="py-6 text-center text-gray-500">{{ __('No payments recorded.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
