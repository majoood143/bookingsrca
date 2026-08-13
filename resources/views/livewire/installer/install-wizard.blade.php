<div class="max-w-2xl mx-auto px-4 sm:px-6 py-10 sm:py-14">

    <div class="text-center mb-8">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ __('installer.title') }}</h1>
        <p class="mt-2 text-gray-500">{{ __('installer.subtitle') }}</p>
    </div>

    {{-- ── Step indicator ── --}}
    <div class="flex items-center justify-between mb-10">
        @foreach (__('installer.steps') as $index => $label)
            @php $n = $index + 1; @endphp
            <div class="flex-1 flex items-center">
                <div class="flex flex-col items-center gap-1 text-center">
                    <div
                        class="h-8 w-8 rounded-full flex items-center justify-center text-xs font-bold
                            {{ $n < $step ? 'bg-green-600 text-white' : ($n === $step ? 'bg-gray-900 text-white' : 'bg-gray-200 text-gray-500') }}">
                        {{ $n < $step ? '✓' : $n }}
                    </div>
                    <span class="text-[11px] text-gray-500 hidden sm:block">{{ $label }}</span>
                </div>
                @if (! $loop->last)
                    <div class="flex-1 h-0.5 mx-1 {{ $n < $step ? 'bg-green-600' : 'bg-gray-200' }}"></div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">

        {{-- ── Step 1: Requirements ── --}}
        @if ($step === 1)
            <h2 class="text-lg font-bold text-gray-900 mb-4">{{ __('installer.requirements.title') }}</h2>

            <div class="space-y-2 mb-6">
                <div class="flex items-center justify-between py-2 border-b border-gray-50">
                    <span class="text-sm text-gray-700">{{ __('installer.requirements.php_version', ['version' => $this->requirements['php']['required']]) }}</span>
                    <span class="text-sm font-semibold {{ $this->requirements['php']['passed'] ? 'text-green-600' : 'text-red-600' }}">
                        {{ $this->requirements['php']['current'] }} {{ $this->requirements['php']['passed'] ? '✓' : '✗' }}
                    </span>
                </div>

                @foreach ($this->requirements['extensions'] as $extension => $loaded)
                    <div class="flex items-center justify-between py-2 border-b border-gray-50">
                        <span class="text-sm text-gray-700">{{ __('installer.requirements.extension', ['name' => $extension]) }}</span>
                        <span class="text-sm font-semibold {{ $loaded ? 'text-green-600' : 'text-red-600' }}">{{ $loaded ? '✓' : '✗' }}</span>
                    </div>
                @endforeach

                @foreach ($this->requirements['writable'] as $path => $writable)
                    <div class="flex items-center justify-between py-2 border-b border-gray-50">
                        <span class="text-sm text-gray-700">{{ __('installer.requirements.writable', ['path' => $path]) }}</span>
                        <span class="text-sm font-semibold {{ $writable ? 'text-green-600' : 'text-red-600' }}">{{ $writable ? '✓' : '✗' }}</span>
                    </div>
                @endforeach

                <div class="flex items-center justify-between py-2">
                    <span class="text-sm text-gray-700">{{ __('installer.requirements.env_writable') }}</span>
                    <span class="text-sm font-semibold {{ $this->requirements['envWritable'] ? 'text-green-600' : 'text-red-600' }}">
                        {{ $this->requirements['envWritable'] ? '✓' : '✗' }}
                    </span>
                </div>
            </div>

            @unless ($this->requirements['passed'])
                <p class="text-sm text-red-600 mb-4">{{ __('installer.requirements.failed_hint') }}</p>
            @endunless

            <div class="flex justify-end">
                <button wire:click="continueFromRequirements" @unless($this->requirements['passed']) disabled @endunless
                    class="px-5 py-2.5 rounded-xl text-sm font-semibold bg-gray-900 text-white disabled:opacity-40 disabled:cursor-not-allowed hover:bg-gray-800 transition-colors">
                    {{ __('installer.actions.continue') }}
                </button>
            </div>
        @endif

        {{-- ── Step 2: Database ── --}}
        @if ($step === 2)
            <h2 class="text-lg font-bold text-gray-900 mb-4">{{ __('installer.database.title') }}</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('installer.database.fields.host') }}</label>
                    <input type="text" wire:model="db_host" class="w-full rounded-lg border-gray-300 text-sm">
                    @error('db_host') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('installer.database.fields.port') }}</label>
                    <input type="text" wire:model="db_port" class="w-full rounded-lg border-gray-300 text-sm">
                    @error('db_port') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('installer.database.fields.database') }}</label>
                    <input type="text" wire:model="db_database" class="w-full rounded-lg border-gray-300 text-sm">
                    @error('db_database') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('installer.database.fields.username') }}</label>
                    <input type="text" wire:model="db_username" class="w-full rounded-lg border-gray-300 text-sm">
                    @error('db_username') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('installer.database.fields.password') }}</label>
                    <input type="password" wire:model="db_password" class="w-full rounded-lg border-gray-300 text-sm">
                </div>
            </div>

            @if ($dbMessage)
                <p class="text-sm mb-4 {{ $dbSuccess ? 'text-green-600' : 'text-red-600' }}">{{ $dbMessage }}</p>
            @endif

            <div class="flex items-center justify-between gap-3">
                <button wire:click="testConnection" wire:loading.attr="disabled" wire:target="testConnection"
                    class="px-4 py-2.5 rounded-xl text-sm font-semibold border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">
                    <span wire:loading.remove wire:target="testConnection">{{ __('installer.actions.test_connection') }}</span>
                    <span wire:loading wire:target="testConnection">{{ __('installer.actions.testing') }}</span>
                </button>

                <button wire:click="saveDatabase" wire:loading.attr="disabled" wire:target="saveDatabase"
                    class="px-5 py-2.5 rounded-xl text-sm font-semibold bg-gray-900 text-white hover:bg-gray-800 transition-colors">
                    {{ __('installer.actions.continue') }}
                </button>
            </div>
        @endif

        {{-- ── Step 3: App settings ── --}}
        @if ($step === 3)
            <h2 class="text-lg font-bold text-gray-900 mb-4">{{ __('installer.app_settings.title') }}</h2>

            <div class="space-y-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('installer.app_settings.fields.app_name') }}</label>
                    <input type="text" wire:model="app_name" class="w-full rounded-lg border-gray-300 text-sm">
                    @error('app_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('installer.app_settings.fields.app_url') }}</label>
                    <input type="text" wire:model="app_url" class="w-full rounded-lg border-gray-300 text-sm">
                    @error('app_url') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex justify-end">
                <button wire:click="saveAppSettings" wire:loading.attr="disabled"
                    class="px-5 py-2.5 rounded-xl text-sm font-semibold bg-gray-900 text-white hover:bg-gray-800 transition-colors">
                    {{ __('installer.actions.continue') }}
                </button>
            </div>
        @endif

        {{-- ── Step 4: Migrate ── --}}
        @if ($step === 4)
            <h2 class="text-lg font-bold text-gray-900 mb-4">{{ __('installer.migrate.title') }}</h2>
            <p class="text-sm text-gray-500 mb-6">{{ __('installer.migrate.description') }}</p>

            @if ($this->isMigrated)
                <p class="text-sm text-green-600 mb-6">{{ __('installer.migrate.already_done') }}</p>
            @elseif ($migrateMessage)
                <p class="text-sm mb-6 {{ $migrateSuccess ? 'text-green-600' : 'text-red-600' }}">{{ $migrateMessage }}</p>
            @endif

            <div class="flex items-center justify-between gap-3">
                <button wire:click="runMigrations" wire:loading.attr="disabled" wire:target="runMigrations"
                    class="px-4 py-2.5 rounded-xl text-sm font-semibold border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">
                    <span wire:loading.remove wire:target="runMigrations">
                        {{ $this->isMigrated ? __('installer.actions.rerun') : __('installer.actions.run') }}
                    </span>
                    <span wire:loading wire:target="runMigrations">{{ __('installer.migrate.running') }}</span>
                </button>

                <button wire:click="continueFromMigrate" @unless($this->isMigrated) disabled @endunless
                    class="px-5 py-2.5 rounded-xl text-sm font-semibold bg-gray-900 text-white disabled:opacity-40 disabled:cursor-not-allowed hover:bg-gray-800 transition-colors">
                    {{ __('installer.actions.continue') }}
                </button>
            </div>
        @endif

        {{-- ── Step 5: Admin account ── --}}
        @if ($step === 5)
            <h2 class="text-lg font-bold text-gray-900 mb-4">{{ __('installer.admin.title') }}</h2>

            @if ($this->existingAdmin)
                <p class="text-sm text-gray-600 mb-6">{{ __('installer.admin.already_exists', ['email' => $this->existingAdmin->email]) }}</p>
                <div class="flex justify-end">
                    <button wire:click="goToStep(6)"
                        class="px-5 py-2.5 rounded-xl text-sm font-semibold bg-gray-900 text-white hover:bg-gray-800 transition-colors">
                        {{ __('installer.actions.continue') }}
                    </button>
                </div>
            @else
                <div class="space-y-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('installer.admin.fields.name') }}</label>
                        <input type="text" wire:model="admin_name" class="w-full rounded-lg border-gray-300 text-sm">
                        @error('admin_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('installer.admin.fields.email') }}</label>
                        <input type="email" wire:model="admin_email" class="w-full rounded-lg border-gray-300 text-sm">
                        @error('admin_email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('installer.admin.fields.password') }}</label>
                        <input type="password" wire:model="admin_password" class="w-full rounded-lg border-gray-300 text-sm">
                        @error('admin_password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('installer.admin.fields.password_confirmation') }}</label>
                        <input type="password" wire:model="admin_password_confirmation" class="w-full rounded-lg border-gray-300 text-sm">
                    </div>
                </div>

                <div class="flex justify-end">
                    <button wire:click="createAdmin" wire:loading.attr="disabled"
                        class="px-5 py-2.5 rounded-xl text-sm font-semibold bg-gray-900 text-white hover:bg-gray-800 transition-colors">
                        {{ __('installer.actions.create_admin') }}
                    </button>
                </div>
            @endif
        @endif

        {{-- ── Step 6: Finish ── --}}
        @if ($step === 6)
            <div class="text-center py-4">
                <div class="text-5xl mb-4">🎉</div>
                <h2 class="text-lg font-bold text-gray-900 mb-2">{{ __('installer.finish.title') }}</h2>
                <p class="text-sm text-gray-500 mb-6">{{ __('installer.finish.description') }}</p>

                <button wire:click="finish" wire:loading.attr="disabled"
                    class="px-6 py-3 rounded-xl text-sm font-semibold bg-gray-900 text-white hover:bg-gray-800 transition-colors">
                    {{ __('installer.actions.finish') }}
                </button>

                <p class="text-xs text-gray-400 mt-6">{{ __('installer.finish.cache_note') }}</p>
            </div>
        @endif

    </div>
</div>
