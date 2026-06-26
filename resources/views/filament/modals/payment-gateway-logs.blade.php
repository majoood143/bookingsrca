<div class="space-y-4 max-h-[70vh] overflow-y-auto">
    @forelse ($logs as $log)
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-xs font-semibold uppercase {{ $log->gateway === 'thawani' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                        {{ $log->gateway }}
                    </span>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">
                        {{ \Illuminate\Support\Str::of($log->event)->replace('_', ' ')->headline() }}
                    </span>
                    @if ($log->status_code)
                        <span class="text-xs px-2 py-0.5 rounded {{ $log->status_code >= 200 && $log->status_code < 300 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            HTTP {{ $log->status_code }}
                        </span>
                    @endif
                </div>
                <span class="text-xs text-gray-500">{{ $log->created_at->format('M d, Y H:i:s') }}</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <div class="text-xs font-semibold text-gray-500 mb-1">{{ __('booking.gateway_logs.request') }}</div>
                    <pre class="text-xs bg-gray-50 dark:bg-gray-800 rounded p-2 overflow-x-auto whitespace-pre-wrap">{{ json_encode($log->request_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
                <div>
                    <div class="text-xs font-semibold text-gray-500 mb-1">{{ __('booking.gateway_logs.response') }}</div>
                    <pre class="text-xs bg-gray-50 dark:bg-gray-800 rounded p-2 overflow-x-auto whitespace-pre-wrap">{{ json_encode($log->response_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
            </div>
        </div>
    @empty
        <div class="text-center text-gray-500 py-8">
            {{ __('booking.gateway_logs.empty') }}
        </div>
    @endforelse
</div>
