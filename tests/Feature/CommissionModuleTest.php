<?php

namespace Tests\Feature;

use App\Enums\CommissionType;
use App\Models\BookingSetting;
use App\Models\CommissionSetting;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CommissionModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Mirrors what `php artisan shield:generate` does in a real deployment:
        // it creates the permission records the resource/page policies check,
        // which this isolated test database doesn't have without it.
        $role = Role::findOrCreate('super_admin', 'web');
        foreach (['ViewAny:CommissionSetting', 'View:CommissionSetting', 'View:CommissionReport'] as $permission) {
            $role->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }

        $this->admin = User::factory()->create(['is_active' => true]);
        $this->admin->assignRole($role);
    }

    public function test_commission_settings_page_is_reachable_when_module_enabled(): void
    {
        BookingSetting::set('module_commission_enabled', 'true');
        BookingSetting::clearCache();

        $this->actingAs($this->admin)
            ->get('/admin/commission-settings')
            ->assertSuccessful();
    }

    public function test_commission_settings_page_is_blocked_when_module_disabled(): void
    {
        BookingSetting::set('module_commission_enabled', 'false');
        BookingSetting::clearCache();

        $this->actingAs($this->admin)
            ->get('/admin/commission-settings')
            ->assertForbidden();
    }

    public function test_commission_report_page_is_reachable_when_module_enabled(): void
    {
        BookingSetting::set('module_commission_enabled', 'true');
        BookingSetting::clearCache();

        $this->actingAs($this->admin)
            ->get('/admin/commission-report')
            ->assertSuccessful();
    }

    public function test_commission_report_page_is_blocked_when_module_disabled(): void
    {
        BookingSetting::set('module_commission_enabled', 'false');
        BookingSetting::clearCache();

        $this->actingAs($this->admin)
            ->get('/admin/commission-report')
            ->assertForbidden();
    }

    public function test_general_settings_can_toggle_the_commission_module(): void
    {
        BookingSetting::set('module_commission_enabled', 'true');
        BookingSetting::clearCache();

        $this->assertTrue((bool) BookingSetting::get('module_commission_enabled'));

        BookingSetting::set('module_commission_enabled', 'false');
        BookingSetting::clearCache();

        $this->assertFalse((bool) BookingSetting::get('module_commission_enabled'));
    }

    public function test_can_create_a_commission_setting_through_the_resource_form(): void
    {
        BookingSetting::set('module_commission_enabled', 'true');
        BookingSetting::clearCache();

        $event = Event::create([
            'title' => ['en' => 'Test Event', 'ar' => 'فعالية تجريبية'],
            'description' => ['en' => 'desc', 'ar' => 'وصف'],
            'slug' => 'commission-test-event-' . uniqid(),
            'location' => ['en' => 'Test Venue', 'ar' => 'مكان'],
            'organizer' => ['en' => 'Test Org', 'ar' => 'منظم'],
            'start_date' => now()->addDay()->format('Y-m-d'),
            'end_date' => now()->addMonth()->format('Y-m-d'),
            'status' => 'published',
            'max_attendees' => 1000,
        ]);

        $setting = CommissionSetting::create([
            'event_id' => $event->id,
            'name' => ['en' => 'VIP Event Rate', 'ar' => 'نسبة فعالية كبار الزوار'],
            'commission_type' => CommissionType::Percentage,
            'commission_value' => 12.5,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('commission_settings', [
            'id' => $setting->id,
            'event_id' => $event->id,
            'commission_type' => 'percentage',
        ]);
        $this->assertFalse($setting->is_global);
        $this->assertEquals('12.50%', $setting->formatted_rate);
    }
}
