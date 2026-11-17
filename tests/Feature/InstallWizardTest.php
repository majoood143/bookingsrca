<?php

namespace Tests\Feature;

use App\Livewire\Installer\InstallWizard;
use App\Models\BookingSetting;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;
use PDO;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * This app's migrations use MySQL-only syntax (e.g. `ALTER TABLE ... MODIFY`),
 * so — unlike the rest of the suite, which runs against sqlite per phpunit.xml —
 * these tests point at a real, disposable MySQL database to match production.
 */
class InstallWizardTest extends TestCase
{
    use RefreshDatabase;

    protected const TEST_DB = 'installer_wizard_test';

    // phpunit.xml's sqlite values, put back afterwards: unsetting them made every
    // later test fall back to .env and run against the real local MySQL database.
    private static array $previousEnv = [];

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        foreach (['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'] as $key) {
            self::$previousEnv[$key] = [getenv($key), $_ENV[$key] ?? null, $_SERVER[$key] ?? null];
        }

        $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
        $pdo->exec('CREATE DATABASE IF NOT EXISTS `'.self::TEST_DB.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        foreach ([
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => '127.0.0.1',
            'DB_PORT' => '3306',
            'DB_DATABASE' => self::TEST_DB,
            'DB_USERNAME' => 'root',
            'DB_PASSWORD' => '',
        ] as $key => $value) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }

    public static function tearDownAfterClass(): void
    {
        $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
        $pdo->exec('DROP DATABASE IF EXISTS `'.self::TEST_DB.'`');

        foreach (self::$previousEnv as $key => [$env, $envArray, $server]) {
            putenv($env === false ? $key : "{$key}={$env}");

            if ($envArray === null) {
                unset($_ENV[$key]);
            } else {
                $_ENV[$key] = $envArray;
            }

            if ($server === null) {
                unset($_SERVER[$key]);
            } else {
                $_SERVER[$key] = $server;
            }
        }

        // The "already migrated" flag describes the MySQL database above, not
        // the fresh sqlite one the next test class gets.
        RefreshDatabaseState::$migrated = false;
        RefreshDatabaseState::$inMemoryConnections = [];

        if (self::$installedMarker !== null) {
            file_put_contents(dirname(__DIR__, 2).'/storage/installed', self::$installedMarker);
            self::$installedMarker = null;
        }

        parent::tearDownAfterClass();
    }

    // The real storage/installed marker, put back after the class: deleting it
    // for good sent every later HTTP test (and the local app) to /install.
    private static ?string $installedMarker = null;

    protected function setUp(): void
    {
        parent::setUp();

        if (self::$installedMarker === null && File::exists(storage_path('installed'))) {
            self::$installedMarker = File::get(storage_path('installed'));
        }

        // Every test here starts from a not-yet-installed app.
        File::delete(storage_path('installed'));
    }

    protected function tearDown(): void
    {
        File::delete(storage_path('installed'));

        parent::tearDown();
    }

    public function test_requirements_step_passes_and_advances(): void
    {
        Livewire::test(InstallWizard::class)
            ->assertSet('step', 1)
            ->call('continueFromRequirements')
            ->assertSet('step', 2);
    }

    public function test_migrate_step_runs_migrations_and_shield_permissions(): void
    {
        Livewire::test(InstallWizard::class)
            ->set('step', 4)
            ->call('runMigrations')
            ->assertSet('migrateSuccess', true)
            ->call('continueFromMigrate')
            ->assertSet('step', 5);

        $this->assertGreaterThan(0, Permission::count());
    }

    public function test_general_settings_step_saves_booking_settings_and_advances(): void
    {
        Artisan::call('migrate', ['--force' => true]);

        Livewire::test(InstallWizard::class)
            ->set('step', 5)
            ->set('site_name_en', 'My Venue')
            ->set('site_name_ar', 'مكاني')
            ->set('currency_code', 'USD')
            ->set('currency_symbol', '$')
            ->set('primary_color', '#112233')
            ->set('panel_primary_color', '#445566')
            ->call('saveGeneralSettings')
            ->assertSet('step', 6);

        $this->assertSame('My Venue', BookingSetting::get('site_name_en'));
        $this->assertSame('USD', BookingSetting::get('currency_code'));
        $this->assertSame('#112233', BookingSetting::get('primary_color'));
    }

    public function test_admin_account_is_created_with_super_admin_role_and_logged_in(): void
    {
        Livewire::test(InstallWizard::class)
            ->set('step', 4)
            ->call('runMigrations')
            ->set('step', 6)
            ->set('admin_name', 'Test Admin')
            ->set('admin_email', 'admin@example.com')
            ->set('admin_password', 'password123')
            ->set('admin_password_confirmation', 'password123')
            ->call('createAdmin')
            ->assertSet('step', 7);

        $admin = User::where('email', 'admin@example.com')->first();

        $this->assertNotNull($admin);
        $this->assertTrue($admin->hasRole('super_admin'));
        $this->assertTrue($admin->is_active);
        $this->assertAuthenticatedAs($admin);
    }

    public function test_existing_admin_is_detected_and_create_form_is_skipped(): void
    {
        Livewire::test(InstallWizard::class)
            ->set('step', 4)
            ->call('runMigrations')
            ->set('step', 6)
            ->set('admin_name', 'Test Admin')
            ->set('admin_email', 'admin@example.com')
            ->set('admin_password', 'password123')
            ->set('admin_password_confirmation', 'password123')
            ->call('createAdmin');

        Livewire::test(InstallWizard::class)
            ->set('step', 6)
            ->assertSee('admin@example.com');
    }

    public function test_finish_writes_lock_file_and_blocks_reaccess(): void
    {
        $this->assertFileDoesNotExist(storage_path('installed'));

        Livewire::test(InstallWizard::class)
            ->set('step', 7)
            ->call('finish');

        $this->assertFileExists(storage_path('installed'));

        // mount() should now bounce anyone hitting /install straight to login.
        Livewire::test(InstallWizard::class)
            ->assertRedirect('/admin/login');
    }

    public function test_finish_seeds_demo_data_when_requested(): void
    {
        Artisan::call('migrate', ['--force' => true]);

        $this->assertSame(0, Event::count());

        Livewire::test(InstallWizard::class)
            ->set('step', 7)
            ->set('seed_demo_data', true)
            ->call('finish');

        $this->assertGreaterThan(0, Event::count());
    }

    public function test_migrated_tables_without_populated_permissions_do_not_unlock_admin_step(): void
    {
        // Regression test: migrate table structure exists but shield:generate
        // never ran (e.g. it failed silently) — this used to still let the
        // wizard through to admin creation, producing a "super admin" with
        // zero real permissions.
        Artisan::call('migrate', ['--force' => true]);

        Livewire::test(InstallWizard::class)
            ->set('step', 4)
            ->assertSet('isMigrated', false)
            ->call('continueFromMigrate')
            ->assertSet('step', 4);
    }

    public function test_admin_password_validation_requires_confirmation_and_minimum_length(): void
    {
        Livewire::test(InstallWizard::class)
            ->set('step', 4)
            ->call('runMigrations')
            ->set('step', 6)
            ->set('admin_name', 'Test Admin')
            ->set('admin_email', 'admin@example.com')
            ->set('admin_password', 'short')
            ->set('admin_password_confirmation', 'short')
            ->call('createAdmin')
            ->assertHasErrors(['admin_password']);
    }
}
