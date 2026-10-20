<?php

namespace Tests\Feature\Games;

use App\Filament\Pages\Settings\GeneralSettings;
use App\Filament\Resources\GameAttemptResource\Pages\ListGameAttempts;
use App\Filament\Resources\GameAttemptResource\Pages\ViewGameAttempt;
use App\Filament\Resources\GameCampaignResource\Pages\CampaignLeaderboard;
use App\Filament\Resources\GameCampaignResource\Pages\CreateGameCampaign;
use App\Filament\Resources\GameCampaignResource\Pages\EditGameCampaign;
use App\Filament\Resources\GameCampaignResource\Pages\ListGameCampaigns;
use App\Filament\Resources\GamePlayerResource\Pages\ListGamePlayers;
use App\Filament\Resources\GamePlayerResource\Pages\ViewGamePlayer;
use App\Filament\Resources\MessageLogResource\Pages\ListMessageLogs;
use App\Models\BookingSetting;
use App\Models\GameAttempt;
use App\Models\GameAward;
use App\Models\GameCampaign;
use App\Models\GamePrize;
use App\Models\User;
use App\Support\MessagingSettings;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class GameAdminTest extends GameTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Mirrors what `php artisan shield:generate` (or the permissions migration on an existing
        // install) gives super_admin; this isolated test database has none of them.
        $role = Role::findOrCreate('super_admin', 'web');
        foreach (['GameCampaign', 'GamePlayer', 'GameAttempt', 'MessageLog'] as $model) {
            foreach (['ViewAny', 'View', 'Create', 'Update', 'Delete'] as $action) {
                $role->givePermissionTo(Permission::findOrCreate("{$action}:{$model}", 'web'));
            }
        }
        $role->givePermissionTo(Permission::findOrCreate('View:GeneralSettings', 'web'));

        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole($role);
        $this->actingAs($admin);
    }

    private function attempt(GameCampaign $campaign, array $overrides = []): GameAttempt
    {
        return GameAttempt::create(array_merge([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'game_campaign_id' => $campaign->id,
            'game_player_id' => $this->player()->id,
            'status' => GameAttempt::STATUS_COMPLETED,
            'seed' => 7,
            'shots_taken' => 5,
            'goals' => 4,
            'started_at' => now()->subMinutes(3),
            'finished_at' => now()->subMinutes(2),
            'duration_ms' => 41370,
        ], $overrides));
    }

    public function test_an_admin_creates_a_branded_campaign_with_prizes(): void
    {
        Livewire::test(CreateGameCampaign::class)
            ->fillForm([
                'name' => ['en' => 'Ramadan Cup', 'ar' => 'كأس رمضان'],
                'slug' => 'ramadan-cup',
                'game_type' => 'penalty',
                'status' => 'published',
                'starts_at' => now()->format('Y-m-d H:i'),
                'ends_at' => now()->addWeek()->format('Y-m-d H:i'),
                'shots_per_attempt' => 5,
                'attempts_per_day' => 3,
                'keeper_skill' => 60,
                'risk_threshold' => 60,
                'leaderboard_size' => 10,
                'primary_color' => '#0b3d91',
                'prizes' => [
                    ['rank' => 1, 'discount_type' => 'percentage', 'discount_value' => 50, 'max_discount_amount' => 20, 'valid_days' => 30],
                    ['rank' => 2, 'discount_type' => 'fixed', 'discount_value' => 5],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $campaign = GameCampaign::where('slug', 'ramadan-cup')->firstOrFail();
        $this->assertSame('كأس رمضان', $campaign->getTranslation('name', 'ar'));
        $this->assertSame('#0b3d91', $campaign->brand()['primary']);
        $this->assertSame([1, 2], $campaign->prizes()->pluck('rank')->all());
        $this->assertEquals(20, $campaign->prizes()->first()->max_discount_amount);
    }

    public function test_the_game_screens_render(): void
    {
        $campaign = $this->campaign();
        $attempt = $this->attempt($campaign, ['flags' => ['straight_path']]);
        $attempt->shots()->create(['n' => 1, 'input' => ['u' => 0.1, 'v' => 0.8, 'flags' => ['straight_path']], 'aim_x' => 0.4, 'aim_y' => 1.9, 'power' => 0.8, 'keeper' => ['x' => 2.5, 'y' => 0.5, 'read' => false], 'outcome' => 'goal']);

        Livewire::test(ListGameCampaigns::class)->assertOk()->assertCanSeeTableRecords([$campaign]);
        Livewire::test(EditGameCampaign::class, ['record' => $campaign->getRouteKey()])->assertOk();
        Livewire::test(CampaignLeaderboard::class, ['record' => $campaign->getRouteKey()])->assertOk()->assertCanSeeTableRecords([$attempt]);
        Livewire::test(ListGamePlayers::class)->assertOk()->assertCanSeeTableRecords([$attempt->player]);
        Livewire::test(ViewGamePlayer::class, ['record' => $attempt->player->getRouteKey()])->assertOk();
        Livewire::test(ListGameAttempts::class)->assertOk();
        Livewire::test(ViewGameAttempt::class, ['record' => $attempt->getRouteKey()])->assertOk()->assertSee(__('games.attempt.flags.straight_path'));
        Livewire::test(ListMessageLogs::class)->assertOk();
    }

    public function test_an_admin_clears_a_flagged_attempt(): void
    {
        $attempt = $this->attempt($this->campaign(), ['review_status' => GameAttempt::REVIEW_FLAGGED, 'risk_score' => 80]);

        Livewire::test(ViewGameAttempt::class, ['record' => $attempt->getRouteKey()])
            ->callAction('clear', ['review_note' => 'Checked the video at the venue'])
            ->assertHasNoFormErrors();

        $attempt->refresh();
        $this->assertSame(GameAttempt::REVIEW_CLEARED, $attempt->review_status);
        $this->assertSame('Checked the video at the venue', $attempt->review_note);
        $this->assertNotNull($attempt->reviewed_by);
    }

    public function test_an_admin_finalizes_an_ended_campaign(): void
    {
        $campaign = $this->campaign(['starts_at' => now()->subWeek(), 'ends_at' => now()->subHour()]);
        GamePrize::create(['game_campaign_id' => $campaign->id, 'rank' => 1, 'discount_type' => 'percentage', 'discount_value' => 50]);
        $this->attempt($campaign);

        Livewire::test(EditGameCampaign::class, ['record' => $campaign->getRouteKey()])
            ->callAction('finalize')
            ->assertHasNoFormErrors();

        $this->assertSame(1, GameAward::count());
        $this->assertTrue($campaign->fresh()->isAwarded());
    }

    public function test_messaging_settings_save_and_the_sms_password_is_encrypted(): void
    {
        Livewire::test(GeneralSettings::class)
            ->assertOk()
            ->set('data.whatsapp_enabled', true)
            ->set('data.otp_sms_fallback_enabled', true)
            ->set('data.sms_driver', 'tamimah')
            ->set('data.sms_tamimah_username', 'club')
            ->set('data.sms_tamimah_password', 's3cret!')
            ->set('data.sms_tamimah_sender', 'CLUB')
            ->set('data.module_games_enabled', false)
            ->call('save');

        $this->assertTrue(MessagingSettings::whatsappEnabled());
        $this->assertTrue(MessagingSettings::smsFallbackEnabled());
        $this->assertSame('tamimah', MessagingSettings::smsDriver());
        $this->assertSame('s3cret!', MessagingSettings::secret('sms_tamimah_password'));
        $this->assertNotSame('s3cret!', BookingSetting::where('key', 'sms_tamimah_password')->value('value'));
        $this->assertFalse((bool) BookingSetting::get('module_games_enabled', true));

        // switching the module off hides the game from the public
        $this->get(route('games.play', $this->campaign()->slug))->assertNotFound();
    }
}
