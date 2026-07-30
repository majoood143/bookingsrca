{{-- Field-set for a single attendee — extracted so both the classic
     all-at-once form (public site) and the kiosk's one-at-a-time
     progressive flow render identical inputs from one source.
     Expects: $i (attendee index), $attendee (attendees.$i array). --}}

{{-- Name --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
            {{ __('event_booking.step5.first_name') }} <span
                class="text-red-500">*</span>
        </label>
        <input type="text"
            wire:model="attendees.{{ $i }}.first_name"
            placeholder="{{ __('event_booking.step5.first_name_placeholder') }}"
            class="w-full px-4 py-2.5 border rounded-xl text-gray-900 placeholder-gray-300 focus:ring-2 focus:ring-brand-hover focus:border-brand-hover outline-none transition
                {{ $errors->has("attendees.$i.first_name") ? 'border-red-400 bg-red-50 ring-1 ring-red-300' : 'border-gray-300' }}">
        @error("attendees.$i.first_name")
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
            {{ __('event_booking.step5.last_name') }} <span
                class="text-red-500">*</span>
        </label>
        <input type="text"
            wire:model="attendees.{{ $i }}.last_name"
            placeholder="{{ __('event_booking.step5.last_name_placeholder') }}"
            class="w-full px-4 py-2.5 border rounded-xl text-gray-900 placeholder-gray-300 focus:ring-2 focus:ring-brand-hover focus:border-brand-hover outline-none transition
                {{ $errors->has("attendees.$i.last_name") ? 'border-red-400 bg-red-50 ring-1 ring-red-300' : 'border-gray-300' }}">
        @error("attendees.$i.last_name")
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>
</div>

{{-- Email --}}
@if ($showEmail)
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
            {{ __('event_booking.step5.email_address') }}
            @if ($requireEmail)
                <span class="text-red-500">*</span>
            @endif
        </label>
        <input type="email"
            wire:model.live="attendees.{{ $i }}.email"
            placeholder="john@example.com"
            {{ $copyContactToAll && $i > 0 ? 'readonly' : '' }}
            class="w-full px-4 py-2.5 border rounded-xl text-gray-900 placeholder-gray-300 focus:ring-2 focus:ring-brand-hover focus:border-brand-hover outline-none transition
                {{ $copyContactToAll && $i > 0 ? 'bg-gray-50 text-gray-500 cursor-not-allowed' : '' }}
                {{ $errors->has("attendees.$i.email") ? 'border-red-400 bg-red-50 ring-1 ring-red-300' : 'border-gray-300' }}">
        @if ($i === 0)
            <p class="text-xs text-gray-400 mt-1.5">
                {{ __('event_booking.step5.email_hint') }}</p>
        @endif
        @error("attendees.$i.email")
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>
@endif

{{-- Phone --}}
@if ($showPhone)
    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
            {{ __('event_booking.step5.phone_number') }}
            @if ($requirePhone)
                <span class="text-red-500">*</span>
            @endif
        </label>
        <input type="tel" pattern="\+?\d{7,15}" inputmode="numeric"
            autocomplete="tel"
            wire:model.live="attendees.{{ $i }}.phone"
            placeholder="+968 00000000"
            {{ $copyContactToAll && $i > 0 ? 'readonly' : '' }}
            class="w-full px-4 py-2.5 border rounded-xl text-gray-900 placeholder-gray-300 focus:ring-2 focus:ring-brand-hover focus:border-brand-hover outline-none transition
                {{ $copyContactToAll && $i > 0 ? 'bg-gray-50 text-gray-500 cursor-not-allowed' : '' }}
                {{ $errors->has("attendees.$i.phone") ? 'border-red-400 bg-red-50 ring-1 ring-red-300' : 'border-gray-300' }}">
        @error("attendees.$i.phone")
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>
@endif

{{-- Optional fields --}}
@if ($showDateOfBirth || $showGender || $showNationality || $showIdentityNumber || $showPassportNumber)
    @php
        $optCount = collect([
            $showDateOfBirth,
            $showGender,
            $showNationality,
            $showIdentityNumber,
            $showPassportNumber,
        ])
            ->filter()
            ->count();
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-{{ $optCount }} gap-4">
        @if ($showDateOfBirth)
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    {{ __('event_booking.step5.date_of_birth') }}
                    @if ($requireDateOfBirth)
                        <span class="text-red-500">*</span>
                    @endif
                </label>
                <input type="date" dir="ltr"
                    wire:model="attendees.{{ $i }}.date_of_birth"
                    min="{{ $minBirthDate }}" max="{{ $maxBirthDate }}"
                    class="w-full px-4 py-2.5 border rounded-xl text-gray-900 text-left focus:ring-2 focus:ring-brand-hover focus:border-brand-hover outline-none transition
                        {{ $errors->has("attendees.$i.date_of_birth") ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                @error("attendees.$i.date_of_birth")
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        @endif
        @if ($showGender)
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    {{ __('event_booking.step5.gender') }}
                    @if ($requireGender)
                        <span class="text-red-500">*</span>
                    @endif
                </label>
                <select wire:model="attendees.{{ $i }}.gender"
                    class="w-full px-4 py-2.5 border rounded-xl text-gray-900 focus:ring-2 focus:ring-brand-hover focus:border-brand-hover outline-none transition
                        {{ $errors->has("attendees.$i.gender") ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                    <option value="">
                        {{ __('event_booking.step5.select_gender') }}</option>
                    <option value="male">{{ __('event_booking.step5.male') }}
                    </option>
                    <option value="female">{{ __('event_booking.step5.female') }}
                    </option>
                </select>
                @error("attendees.$i.gender")
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        @endif
        @if ($showNationality)
            <div class="relative" x-data="{
                open: false,
                search: '',
                options: @js(__('booking.options.nationality')),
                get filtered() {
                    if (!this.search.trim()) return this.options;
                    const term = this.search.toLowerCase();
                    return Object.fromEntries(Object.entries(this.options).filter(([code, label]) => label.toLowerCase().includes(term)));
                },
                choose(code, label) {
                    this.search = label;
                    this.open = false;
                    $wire.set('attendees.{{ $i }}.nationality', code);
                },
            }"
                x-init="search = options[$wire.attendees[{{ $i }}].nationality] ?? ''">
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    {{ __('event_booking.step5.nationality') }}
                    @if ($requireNationality)
                        <span class="text-red-500">*</span>
                    @endif
                </label>
                <input type="text" x-model="search" @focus="open = true"
                    @input="open = true" @keydown.escape="open = false"
                    @click.outside="open = false" autocomplete="off"
                    placeholder="{{ __('booking.placeholders.nationality') }}"
                    class="w-full px-4 py-2.5 border rounded-xl text-gray-900 placeholder-gray-300 focus:ring-2 focus:ring-brand-hover focus:border-brand-hover outline-none transition
                        {{ $errors->has("attendees.$i.nationality") ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                <div x-show="open" x-transition style="display: none;"
                    class="absolute z-30 mt-1 w-full max-h-56 overflow-y-auto bg-white border border-gray-200 rounded-xl shadow-lg">
                    <template x-for="[code, label] in Object.entries(filtered)"
                        :key="code">
                        <div @click="choose(code, label)"
                            class="px-4 py-2 text-sm text-gray-700 hover:bg-brand-hover/10 cursor-pointer"
                            x-text="label"></div>
                    </template>
                    <div x-show="Object.keys(filtered).length === 0"
                        class="px-4 py-2 text-sm text-gray-400">
                        {{ __('event_booking.step5.nationality_no_results') }}
                    </div>
                </div>
                @error("attendees.$i.nationality")
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        @endif
        @if ($showIdentityNumber)
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    {{ __('event_booking.step5.identity_number') }}
                    @if ($requireIdentityNumber)
                        <span class="text-red-500">*</span>
                    @endif
                </label>
                <input type="text"
                    wire:model="attendees.{{ $i }}.identity_number"
                    placeholder="{{ __('booking.placeholders.identity_number') }}"
                    class="w-full px-4 py-2.5 border rounded-xl text-gray-900 placeholder-gray-300 focus:ring-2 focus:ring-brand-hover focus:border-brand-hover outline-none transition
                        {{ $errors->has("attendees.$i.identity_number") ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                @error("attendees.$i.identity_number")
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        @endif
        @if ($showPassportNumber)
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    {{ __('event_booking.step5.passport_number') }}
                    @if ($requirePassportNumber)
                        <span class="text-red-500">*</span>
                    @endif
                </label>
                <input type="text"
                    wire:model="attendees.{{ $i }}.passport_number"
                    placeholder="{{ __('booking.placeholders.passport_number') }}"
                    class="w-full px-4 py-2.5 border rounded-xl text-gray-900 placeholder-gray-300 focus:ring-2 focus:ring-brand-hover focus:border-brand-hover outline-none transition
                        {{ $errors->has("attendees.$i.passport_number") ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                @error("attendees.$i.passport_number")
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        @endif
    </div>
@endif

{{-- Document uploads --}}
@if ($showIdentityCardUpload || $showPassportUpload)
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @if ($showIdentityCardUpload)
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    {{ __('event_booking.step5.identity_card_upload') }}
                    @if ($requireIdentityCardUpload)
                        <span class="text-red-500">*</span>
                    @endif
                </label>
                <input type="file" accept="image/*"
                    wire:model="attendees.{{ $i }}.identity_card_upload"
                    class="w-full px-3 py-2 border rounded-xl text-gray-900 text-sm file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-brand file:text-white file:text-sm file:font-medium file:cursor-pointer hover:file:bg-brand-hover focus:ring-2 focus:ring-brand-hover focus:border-brand-hover outline-none transition
                        {{ $errors->has("attendees.$i.identity_card_upload") ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                <div wire:loading wire:target="attendees.{{ $i }}.identity_card_upload"
                    class="text-xs text-gray-400 mt-1">
                    {{ __('event_booking.step5.uploading') }}
                </div>
                @if (!empty($attendee['identity_card_upload']) && is_object($attendee['identity_card_upload']))
                    <p class="text-xs text-green-600 mt-1">
                        {{ __('event_booking.step5.file_selected', ['name' => $attendee['identity_card_upload']->getClientOriginalName()]) }}
                    </p>
                @endif
                @error("attendees.$i.identity_card_upload")
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        @endif
        @if ($showPassportUpload)
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    {{ __('event_booking.step5.passport_upload') }}
                    @if ($requirePassportUpload)
                        <span class="text-red-500">*</span>
                    @endif
                </label>
                <input type="file" accept="image/*"
                    wire:model="attendees.{{ $i }}.passport_upload"
                    class="w-full px-3 py-2 border rounded-xl text-gray-900 text-sm file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-brand file:text-white file:text-sm file:font-medium file:cursor-pointer hover:file:bg-brand-hover focus:ring-2 focus:ring-brand-hover focus:border-brand-hover outline-none transition
                        {{ $errors->has("attendees.$i.passport_upload") ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                <div wire:loading wire:target="attendees.{{ $i }}.passport_upload"
                    class="text-xs text-gray-400 mt-1">
                    {{ __('event_booking.step5.uploading') }}
                </div>
                @if (!empty($attendee['passport_upload']) && is_object($attendee['passport_upload']))
                    <p class="text-xs text-green-600 mt-1">
                        {{ __('event_booking.step5.file_selected', ['name' => $attendee['passport_upload']->getClientOriginalName()]) }}
                    </p>
                @endif
                @error("attendees.$i.passport_upload")
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
        @endif
    </div>
@endif
