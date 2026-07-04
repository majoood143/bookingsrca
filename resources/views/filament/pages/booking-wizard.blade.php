<x-filament-panels::page>
    @if ($createdBooking)
        <div class="flex flex-col items-center gap-6 py-8">
            <div class="flex flex-col items-center gap-2 text-center">
                <x-filament::icon icon="heroicon-o-check-circle" class="h-20 w-20 text-success-500" />
                <h2 class="text-2xl font-bold text-gray-950 dark:text-white">{{ __('booking.wizard.success_heading') }}</h2>
                <p class="text-gray-500 dark:text-gray-400">{{ __('booking.wizard.success_subheading') }}</p>
            </div>

            <x-filament::section class="w-full max-w-2xl">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="block text-gray-500 dark:text-gray-400">{{ __('booking.fields.booking_reference') }}</span>
                        <span class="block text-lg font-bold text-gray-950 dark:text-white">{{ $createdBooking->booking_reference }}</span>
                    </div>
                    <div>
                        <span class="block text-gray-500 dark:text-gray-400">{{ __('booking.fields.event') }}</span>
                        <span class="block text-lg font-semibold text-gray-950 dark:text-white">{{ $createdBooking->event->getTranslation('title', app()->getLocale()) }}</span>
                    </div>
                    <div>
                        <span class="block text-gray-500 dark:text-gray-400">{{ __('booking.fields.event_date') }}</span>
                        <span class="block text-gray-950 dark:text-white">{{ $createdBooking->event_date->format('Y-m-d') }} &middot; {{ $createdBooking->timeSlot->getTimeRange() }}</span>
                    </div>
                    <div>
                        <span class="block text-gray-500 dark:text-gray-400">{{ __('booking.columns.attendees') }}</span>
                        <span class="block text-gray-950 dark:text-white">{{ $createdBooking->attendees->count() }}</span>
                    </div>
                    <div>
                        <span class="block text-gray-500 dark:text-gray-400">{{ __('booking.payments.summary.total_paid') }}</span>
                        <span class="block font-semibold text-success-600">OMR {{ number_format($createdBooking->total_paid, 3) }}</span>
                    </div>
                    <div>
                        <span class="block text-gray-500 dark:text-gray-400">{{ __('booking.payments.summary.balance_due') }}</span>
                        <span class="block font-semibold {{ $createdBooking->balance_due > 0 ? 'text-danger-600' : 'text-success-600' }}">OMR {{ number_format($createdBooking->balance_due, 3) }}</span>
                    </div>
                    <div class="col-span-2 border-t border-gray-100 pt-3 dark:border-gray-700">
                        <span class="block text-gray-500 dark:text-gray-400">{{ __('booking.fields.total_price') }}</span>
                        <span class="block text-2xl font-bold text-success-600">OMR {{ number_format($createdBooking->total_price, 3) }}</span>
                    </div>
                </div>
            </x-filament::section>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('bookings.receipt', $createdBooking) }}" target="_blank" rel="noopener">
                    <x-filament::button size="xl" icon="heroicon-o-printer" color="primary" tag="span">
                        {{ __('booking.wizard.print_receipt') }}
                    </x-filament::button>
                </a>

                <a href="{{ route('bookings.attendee-tickets', $createdBooking) }}" target="_blank" rel="noopener">
                    <x-filament::button size="xl" icon="heroicon-o-ticket" color="warning" tag="span">
                        {{ __('booking.wizard.print_tickets') }}
                    </x-filament::button>
                </a>

                <a href="{{ $this->getViewBookingUrl() }}">
                    <x-filament::button size="xl" icon="heroicon-o-eye" color="gray" outlined tag="span">
                        {{ __('booking.wizard.view_booking') }}
                    </x-filament::button>
                </a>

                

                <x-filament::button size="xl" icon="heroicon-o-plus-circle" color="success" wire:click="startNewBooking">
                    {{ __('booking.wizard.new_booking') }}
                </x-filament::button>
            </div>
        </div>
    @else
        <form wire:submit="createBooking">
            {{ $this->form }}
        </form>
    @endif
</x-filament-panels::page>
