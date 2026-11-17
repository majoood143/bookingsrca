<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class EnsureAppIsInstalledMiddlewareTest extends TestCase
{
    // The real storage/installed marker, put back after the class: deleting it
    // for good sent every later HTTP test (and the local app) to /install.
    private static ?string $installedMarker = null;

    protected function setUp(): void
    {
        parent::setUp();

        if (self::$installedMarker === null && File::exists(storage_path('installed'))) {
            self::$installedMarker = File::get(storage_path('installed'));
        }
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$installedMarker !== null) {
            file_put_contents(dirname(__DIR__, 2).'/storage/installed', self::$installedMarker);
            self::$installedMarker = null;
        }

        parent::tearDownAfterClass();
    }

    protected function tearDown(): void
    {
        File::delete(storage_path('installed'));

        parent::tearDown();
    }

    public function test_public_route_is_redirected_to_install_when_not_installed(): void
    {
        File::delete(storage_path('installed'));

        $this->get('/')->assertRedirect('/install');
    }

    public function test_livewires_custom_events_prefixed_update_route_is_not_blocked(): void
    {
        File::delete(storage_path('installed'));

        // Mirrors the custom route AppServiceProvider registers for Livewire's
        // AJAX updates (rerouted under /events for the subdirectory deploy).
        // A real payload isn't needed — only that it isn't bounced to /install.
        $response = $this->withHeaders(['X-Livewire' => 'true'])
            ->post('/events/livewire/update', []);

        $redirectedToInstall = $response->isRedirect()
            && str_ends_with(rtrim((string) $response->headers->get('Location'), '/'), '/install');

        $this->assertFalse($redirectedToInstall, 'Livewire update request was wrongly redirected to /install.');
    }

    public function test_install_route_itself_is_reachable_when_not_installed(): void
    {
        File::delete(storage_path('installed'));

        $this->get('/install')->assertOk();
    }

    public function test_public_route_is_not_redirected_once_installed(): void
    {
        File::put(storage_path('installed'), now()->toDateTimeString());

        $this->get('/install')->assertRedirect('/admin/login');
    }
}
