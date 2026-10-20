<?php

namespace Tests\Feature\Games;

use App\Models\BookingSetting;
use App\Models\GamePlayer;
use App\Models\MessageLog;
use App\Models\PhoneOtp;

class GameSignUpTest extends GameTestCase
{
    private function requestCode(array $overrides = [])
    {
        return $this->postJson(route('games.api.code', 'penalty'), array_merge([
            'name' => 'Salim',
            'mobile' => '9551 2928',
            'accept_terms' => true,
            'marketing_opt_in' => true,
        ], $overrides));
    }

    public function test_the_game_page_renders_with_the_campaign_branding(): void
    {
        $this->campaign(['primary_color' => '#123456']);

        $this->withSession(['locale' => 'en'])
            ->get(route('games.play', 'penalty'))
            ->assertOk()
            ->assertSee('Penalty Challenge')
            ->assertSee('--brand: #123456', false);
    }

    public function test_draft_campaigns_are_hidden_from_the_public(): void
    {
        $this->campaign(['status' => 'draft']);

        $this->get(route('games.play', 'penalty'))->assertNotFound();
    }

    public function test_a_player_signs_up_with_a_one_time_code(): void
    {
        $this->campaign();

        $code = $this->requestCode()->assertOk()->assertJsonPath('status', 'sent')->json('demo_code');
        $this->assertMatchesRegularExpression('/^\d{4}$/', $code);

        // the code itself is never stored or logged
        $this->assertNull(MessageLog::first()->message);
        $this->assertNotSame($code, PhoneOtp::first()->code_hash);

        $token = session()->token();

        $this->postJson(route('games.api.verify', 'penalty'), ['code' => $code])
            ->assertOk()
            ->assertJsonPath('player.mobile', '955**928')
            ->assertJsonPath('player.remaining.today', 3);

        // the page keeps using its CSRF token for the next call (starting the attempt)
        $this->assertSame($token, session()->token());

        $player = GamePlayer::firstOrFail();
        $this->assertSame('96895512928', $player->mobile);
        $this->assertTrue($player->marketing_opt_in);
        $this->assertNotNull($player->verified_at);
    }

    public function test_a_wrong_code_does_not_sign_in_and_tries_are_limited(): void
    {
        $this->campaign();
        $code = $this->requestCode()->json('demo_code');
        $wrong = $code === '0000' ? '1111' : '0000';

        for ($i = 0; $i < 5; $i++) {
            $this->postJson(route('games.api.verify', 'penalty'), ['code' => $wrong])->assertStatus(422)->assertJsonPath('error', 'code_wrong');
        }

        // after five wrong tries even the right code is refused
        $this->postJson(route('games.api.verify', 'penalty'), ['code' => $code])->assertJsonPath('error', 'code_expired');
        $this->assertSame(0, GamePlayer::count());
    }

    public function test_only_omani_mobiles_and_accepted_terms_are_allowed(): void
    {
        $this->campaign();

        $this->requestCode(['mobile' => '2455 1234'])->assertStatus(422)->assertJsonValidationErrors('mobile');
        $this->requestCode(['accept_terms' => false])->assertStatus(422)->assertJsonValidationErrors('accept_terms');
    }

    public function test_codes_cannot_be_requested_again_straight_away(): void
    {
        $this->campaign();

        $this->requestCode()->assertOk();
        $this->requestCode()->assertStatus(429)->assertJsonPath('error', 'throttled');
    }

    public function test_no_code_is_sent_when_no_messaging_channel_is_on(): void
    {
        $this->campaign();
        BookingSetting::set('otp_sms_fallback_enabled', 'false');

        $this->requestCode()->assertStatus(422)->assertJsonPath('error', 'unavailable');
    }
}
