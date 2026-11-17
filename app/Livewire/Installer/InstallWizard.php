<?php

namespace App\Livewire\Installer;

use App\Http\Middleware\EnsureAppIsInstalled;
use App\Models\BookingSetting;
use App\Models\User;
use App\Support\Installer\DatabaseConnectionTester;
use App\Support\Installer\EnvironmentFileWriter;
use App\Support\Installer\RequirementsChecker;
use Database\Seeders\EventSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Session;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class InstallWizard extends Component
{
    use WithFileUploads;

    #[Session]
    public int $step = 1;

    // Step 2 — Database
    public string $db_host = '127.0.0.1';

    public string $db_port = '3306';

    public string $db_database = '';

    public string $db_username = '';

    public string $db_password = '';

    public ?string $dbMessage = null;

    public bool $dbSuccess = false;

    // Step 3 — App settings
    public string $app_name = '';

    public string $app_url = '';

    // Step 4 — Migrate & permissions
    public ?string $migrateMessage = null;

    public bool $migrateSuccess = false;

    // Step 5 — General settings
    public string $site_name_en = '';

    public string $site_name_ar = '';

    public string $currency_code = 'OMR';

    public string $currency_symbol = 'OMR';

    public string $primary_color = '#05602b';

    public string $panel_primary_color = '#16a34a';

    public $site_logo = null;

    public $app_logo = null;

    // Step 6 — Admin account
    public string $admin_name = '';

    public string $admin_email = '';

    public string $admin_password = '';

    public string $admin_password_confirmation = '';

    // Step 7 — Finish
    public bool $seed_demo_data = false;

    // Runs on every follow-up Livewire request: a stale or replayed snapshot
    // must not reach saveDatabase()/createAdmin() once the app is installed.
    public function hydrate(): void
    {
        if (EnsureAppIsInstalled::isInstalled()) {
            abort(403);
        }
    }

    public function mount(): void
    {
        if (EnsureAppIsInstalled::isInstalled()) {
            $this->redirect('/admin/login');

            return;
        }

        $writer = new EnvironmentFileWriter;
        $this->db_host = $writer->get('DB_HOST') ?: $this->db_host;
        $this->db_port = $writer->get('DB_PORT') ?: $this->db_port;
        $this->db_database = $writer->get('DB_DATABASE') ?: '';
        $this->db_username = $writer->get('DB_USERNAME') ?: '';
        $this->app_name = $writer->get('APP_NAME') ?: config('app.name', '');
        $this->app_url = $writer->get('APP_URL') ?: request()->getSchemeAndHttpHost();

        $this->site_name_en = $this->app_name ?: 'Bookings';

        // booking_settings only exists once migrations have run — re-reads
        // whatever was saved previously if the wizard is revisited (e.g. the
        // page was refreshed after step 4), falling back to the defaults
        // above on a genuinely fresh install.
        try {
            if (Schema::hasTable('booking_settings')) {
                $this->site_name_en = BookingSetting::get('site_name_en', $this->site_name_en);
                $this->site_name_ar = BookingSetting::get('site_name_ar', $this->site_name_ar);
                $this->currency_code = BookingSetting::get('currency_code', $this->currency_code);
                $this->currency_symbol = BookingSetting::get('currency_symbol', $this->currency_symbol);
                $this->primary_color = BookingSetting::get('primary_color', $this->primary_color);
                $this->panel_primary_color = BookingSetting::get('panel_primary_color', $this->panel_primary_color);
            }
        } catch (\Throwable) {
            // Leave defaults as set above.
        }
    }

    public function getRequirementsProperty(): array
    {
        return (new RequirementsChecker)->check();
    }

    public function getIsMigratedProperty(): bool
    {
        try {
            // Also requires permissions to actually be populated, not just the
            // table to exist — a table with zero rows means shield:generate
            // didn't complete, and a "super admin" created against it would
            // have no real permissions despite the role name.
            return Schema::hasTable('users')
                && Schema::hasTable('permissions')
                && Permission::count() > 0;
        } catch (\Throwable) {
            return false;
        }
    }

    public function getExistingAdminProperty(): ?User
    {
        if (! $this->isMigrated) {
            return null;
        }

        try {
            return User::role('super_admin')->first();
        } catch (\Throwable) {
            return null;
        }
    }

    public function goToStep(int $step): void
    {
        $this->step = max(1, min(7, $step));
    }

    // ── Step 1 ──────────────────────────────────────────────────────────
    public function continueFromRequirements(): void
    {
        if (! $this->requirements['passed']) {
            return;
        }

        $this->goToStep(2);
    }

    // ── Step 2 ──────────────────────────────────────────────────────────
    public function testConnection(): void
    {
        $this->validate([
            'db_host' => ['required', 'string'],
            'db_port' => ['required', 'numeric'],
            'db_database' => ['required', 'string', 'regex:/^[A-Za-z0-9_]+$/'],
            'db_username' => ['required', 'string'],
        ], [], [
            'db_host' => __('installer.database.fields.host'),
            'db_port' => __('installer.database.fields.port'),
            'db_database' => __('installer.database.fields.database'),
            'db_username' => __('installer.database.fields.username'),
        ]);

        $result = (new DatabaseConnectionTester)->test(
            $this->db_host,
            $this->db_port,
            $this->db_database,
            $this->db_username,
            $this->db_password,
        );

        $this->dbSuccess = $result['success'];
        $this->dbMessage = $result['success']
            ? __($result['created'] ? 'installer.database.created' : 'installer.database.connected')
            : $result['message'];
    }

    public function saveDatabase(): void
    {
        $this->testConnection();

        if (! $this->dbSuccess) {
            return;
        }

        $writer = new EnvironmentFileWriter;
        $writer->set('DB_CONNECTION', 'mysql')
            ->set('DB_HOST', $this->db_host)
            ->set('DB_PORT', $this->db_port)
            ->set('DB_DATABASE', $this->db_database)
            ->set('DB_USERNAME', $this->db_username)
            ->set('DB_PASSWORD', $this->db_password)
            ->save();

        Artisan::call('config:clear');

        $this->goToStep(3);
    }

    // ── Step 3 ──────────────────────────────────────────────────────────
    public function saveAppSettings(): void
    {
        $this->validate([
            'app_name' => ['required', 'string', 'max:255'],
            'app_url' => ['required', 'url'],
        ]);

        $writer = new EnvironmentFileWriter;
        $writer->set('APP_NAME', $this->app_name)
            ->set('APP_URL', rtrim($this->app_url, '/'))
            ->set('APP_ENV', 'production')
            ->set('APP_DEBUG', 'false')
            ->save();

        if (! $writer->get('APP_KEY')) {
            Artisan::call('key:generate', ['--force' => true]);
        }

        Artisan::call('config:clear');

        $this->goToStep(4);
    }

    // ── Step 4 ──────────────────────────────────────────────────────────
    public function runMigrations(): void
    {
        @set_time_limit(300);

        try {
            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('shield:generate', [
                '--panel' => 'admin',
                '--option' => 'permissions',
                '--all' => true,
            ]);

            if (Permission::count() === 0) {
                throw new \RuntimeException('shield:generate ran but produced no permission records — the admin account created next would have no real access.');
            }

            $this->migrateSuccess = true;
            $this->migrateMessage = __('installer.migrate.success');
            unset($this->isMigrated);
        } catch (\Throwable $e) {
            report($e);

            $this->migrateSuccess = false;
            $this->migrateMessage = __('installer.migrate.failed', ['error' => $e->getMessage()]);
        }
    }

    public function continueFromMigrate(): void
    {
        if (! $this->isMigrated) {
            return;
        }

        $this->goToStep(5);
    }

    // ── Step 5 ──────────────────────────────────────────────────────────
    public function saveGeneralSettings(): void
    {
        $this->validate([
            'site_name_en' => ['required', 'string', 'max:255'],
            'site_name_ar' => ['required', 'string', 'max:255'],
            'currency_code' => ['required', 'string', 'max:10'],
            'currency_symbol' => ['required', 'string', 'max:10'],
            'primary_color' => ['required', 'string', 'max:20'],
            'panel_primary_color' => ['required', 'string', 'max:20'],
            'site_logo' => ['nullable', 'image', 'max:2048'],
            'app_logo' => ['nullable', 'image', 'max:2048'],
        ]);

        BookingSetting::set('site_name_en', $this->site_name_en);
        BookingSetting::set('site_name_ar', $this->site_name_ar);
        BookingSetting::set('currency_code', $this->currency_code);
        BookingSetting::set('currency_symbol', $this->currency_symbol);
        BookingSetting::set('primary_color', $this->primary_color);
        BookingSetting::set('panel_primary_color', $this->panel_primary_color);

        if ($this->site_logo) {
            BookingSetting::set('site_logo', $this->site_logo->store('branding', 'public'));
        }

        if ($this->app_logo) {
            BookingSetting::set('app_logo', $this->app_logo->store('branding', 'public'));
        }

        BookingSetting::clearCache();

        $this->goToStep(6);
    }

    // ── Step 6 ──────────────────────────────────────────────────────────
    public function createAdmin(): void
    {
        $this->validate([
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'admin_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $this->admin_name,
            'email' => $this->admin_email,
            'password' => $this->admin_password,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        Artisan::call('shield:super-admin', [
            '--user' => $user->id,
            '--panel' => 'admin',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Auth::login($user);

        $this->goToStep(7);
    }

    // ── Step 7 ──────────────────────────────────────────────────────────
    public function finish(): void
    {
        if ($this->seed_demo_data) {
            try {
                Artisan::call('db:seed', [
                    '--class' => EventSeeder::class,
                    '--force' => true,
                ]);
            } catch (\Throwable $e) {
                // Demo data is a convenience, not a requirement — don't block
                // finishing the install over it, just log for later diagnosis.
                report($e);
            }
        }

        File::put(storage_path('installed'), now()->toDateTimeString());

        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('cache:clear');

        $this->redirect('/admin', navigate: false);
    }

    public function render()
    {
        return view('livewire.installer.install-wizard');
    }
}
