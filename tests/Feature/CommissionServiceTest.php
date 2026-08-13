<?php

namespace Tests\Feature;

use App\Enums\CommissionType;
use App\Models\CommissionSetting;
use App\Models\Event;
use App\Services\CommissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommissionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CommissionService $service;
    protected Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(CommissionService::class);

        $this->event = Event::create([
            'title' => ['en' => 'Test Event', 'ar' => 'فعالية تجريبية'],
            'description' => ['en' => 'desc', 'ar' => 'وصف'],
            'slug' => 'test-event-' . uniqid(),
            'location' => ['en' => 'Test Venue', 'ar' => 'مكان'],
            'organizer' => ['en' => 'Test Org', 'ar' => 'منظم'],
            'start_date' => now()->addDay()->format('Y-m-d'),
            'end_date' => now()->addMonth()->format('Y-m-d'),
            'status' => 'published',
            'max_attendees' => 1000,
        ]);
    }

    public function test_resolves_global_commission_when_no_event_specific_rate_exists(): void
    {
        $global = CommissionSetting::create([
            'event_id' => null,
            'name' => ['en' => 'Global Rate', 'ar' => 'النسبة العامة'],
            'commission_type' => CommissionType::Percentage,
            'commission_value' => 10,
            'is_active' => true,
        ]);

        $resolved = $this->service->resolveForEvent($this->event);

        $this->assertNotNull($resolved);
        $this->assertTrue($resolved->is($global));
    }

    public function test_event_specific_commission_takes_priority_over_global(): void
    {
        CommissionSetting::create([
            'event_id' => null,
            'name' => ['en' => 'Global Rate', 'ar' => 'النسبة العامة'],
            'commission_type' => CommissionType::Percentage,
            'commission_value' => 10,
            'is_active' => true,
        ]);

        $eventSpecific = CommissionSetting::create([
            'event_id' => $this->event->id,
            'name' => ['en' => 'Event Rate', 'ar' => 'نسبة الفعالية'],
            'commission_type' => CommissionType::Percentage,
            'commission_value' => 15,
            'is_active' => true,
        ]);

        $resolved = $this->service->resolveForEvent($this->event);

        $this->assertNotNull($resolved);
        $this->assertTrue($resolved->is($eventSpecific));
    }

    public function test_inactive_settings_are_ignored(): void
    {
        CommissionSetting::create([
            'event_id' => null,
            'name' => ['en' => 'Inactive', 'ar' => 'غير نشط'],
            'commission_type' => CommissionType::Percentage,
            'commission_value' => 10,
            'is_active' => false,
        ]);

        $this->assertNull($this->service->resolveForEvent($this->event));
    }

    public function test_settings_outside_their_effective_range_are_ignored(): void
    {
        CommissionSetting::create([
            'event_id' => null,
            'name' => ['en' => 'Future Rate', 'ar' => 'نسبة مستقبلية'],
            'commission_type' => CommissionType::Percentage,
            'commission_value' => 10,
            'is_active' => true,
            'effective_from' => now()->addMonth()->toDateString(),
        ]);

        $this->assertNull($this->service->resolveForEvent($this->event, now()->toDateString()));

        $this->assertNotNull(
            $this->service->resolveForEvent($this->event, now()->addMonths(2)->toDateString())
        );
    }

    public function test_calculates_percentage_commission(): void
    {
        $setting = CommissionSetting::create([
            'event_id' => null,
            'name' => ['en' => 'Global Rate', 'ar' => 'النسبة العامة'],
            'commission_type' => CommissionType::Percentage,
            'commission_value' => 10,
            'is_active' => true,
        ]);

        $this->assertEquals(20.0, $this->service->calculate(200, $setting));
    }

    public function test_calculates_fixed_commission_capped_at_base_amount(): void
    {
        $setting = CommissionSetting::create([
            'event_id' => null,
            'name' => ['en' => 'Fixed Rate', 'ar' => 'نسبة ثابتة'],
            'commission_type' => CommissionType::Fixed,
            'commission_value' => 5,
            'is_active' => true,
        ]);

        $this->assertEquals(5.0, $this->service->calculate(200, $setting));
        // Fixed commission never exceeds the base amount it's taken from.
        $this->assertEquals(2.0, $this->service->calculate(2, $setting));
    }

    public function test_resolve_and_calculate_returns_zero_when_no_setting_applies(): void
    {
        $result = $this->service->resolveAndCalculate($this->event, 100);

        $this->assertNull($result['setting']);
        $this->assertSame(0.0, $result['amount']);
    }
}
