<?php

namespace Tests\Feature;

use App\Models\Translation;
use App\Support\TranslationCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TranslationOverrideTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_migration_seeds_the_home_hero_with_the_current_text(): void
    {
        $heading = Translation::where(['group' => 'home', 'key' => 'hero.heading'])->firstOrFail();

        $this->assertSame(__('home.hero.heading', [], 'en'), $heading->text['en']);
        $this->assertSame(__('home.hero.heading', [], 'ar'), $heading->text['ar']);
        $this->assertTrue(Translation::where(['group' => 'home', 'key' => 'hero.subheading'])->exists());
    }

    /**
     * The migration seeds the hero rows; start every other test from a clean table.
     */
    protected function withoutSeededRows(): void
    {
        Translation::query()->delete();
    }

    public function test_file_translation_is_used_when_there_is_no_override(): void
    {
        $this->withoutSeededRows();

        $this->assertSame(
            'Discover & Book Amazing Experiences',
            __('home.hero.heading', [], 'en'),
        );
    }

    public function test_override_wins_over_the_lang_file_per_locale(): void
    {
        $this->withoutSeededRows();

        Translation::create([
            'group' => 'home',
            'key' => 'hero.heading',
            'text' => ['en' => 'Custom EN', 'ar' => 'عنوان مخصص'],
        ]);

        $this->assertSame('Custom EN', __('home.hero.heading', [], 'en'));
        $this->assertSame('عنوان مخصص', __('home.hero.heading', [], 'ar'));
    }

    public function test_untouched_keys_and_blank_locales_fall_back_to_the_file(): void
    {
        $this->withoutSeededRows();

        Translation::create([
            'group' => 'home',
            'key' => 'hero.heading',
            'text' => ['en' => 'Custom EN', 'ar' => ''],
        ]);

        $this->assertSame(__('home.hero.subheading', [], 'en'), 'Browse our upcoming events and reserve your spot in just a few clicks.');
        $this->assertNotSame('', __('home.hero.heading', [], 'ar'));
        $this->assertNotSame('hero.heading', __('home.hero.heading', [], 'ar'));
    }

    public function test_json_phrases_can_be_overridden(): void
    {
        $this->withoutSeededRows();

        $phrase = 'Payment was cancelled. Please try again.';

        Translation::create([
            'group' => '*',
            'key' => $phrase,
            'text' => ['en' => 'Payment cancelled, retry.'],
        ]);

        $this->assertSame('Payment cancelled, retry.', __($phrase, [], 'en'));
    }

    public function test_placeholders_still_work_in_overrides(): void
    {
        $this->withoutSeededRows();

        Translation::create([
            'group' => 'home',
            'key' => 'hero.heading',
            'text' => ['en' => 'Hello :name'],
        ]);

        $this->assertSame('Hello Sam', __('home.hero.heading', ['name' => 'Sam'], 'en'));
    }

    public function test_editing_and_deleting_take_effect_immediately(): void
    {
        $this->withoutSeededRows();

        $row = Translation::create([
            'group' => 'home',
            'key' => 'hero.heading',
            'text' => ['en' => 'First'],
        ]);

        $this->assertSame('First', __('home.hero.heading', [], 'en'));

        $row->update(['text' => ['en' => 'Second']]);
        $this->assertSame('Second', __('home.hero.heading', [], 'en'));

        $row->delete();
        $this->assertSame('Discover & Book Amazing Experiences', __('home.hero.heading', [], 'en'));
    }

    public function test_arabic_text_is_stored_unescaped_so_it_can_be_searched(): void
    {
        $this->withoutSeededRows();

        $row = Translation::create([
            'group' => 'home',
            'key' => 'hero.heading',
            'text' => ['ar' => 'مرحبا'],
        ]);

        $this->assertStringContainsString('مرحبا', $row->getRawOriginal('text'));
    }

    public function test_the_catalog_lists_lang_file_keys_and_ignores_overrides(): void
    {
        $this->withoutSeededRows();

        Translation::create(['group' => 'home', 'key' => 'hero.heading', 'text' => ['en' => 'Custom EN']]);
        TranslationCatalog::flush();

        $entry = TranslationCatalog::find('home', 'hero.heading');

        $this->assertSame('Discover & Book Amazing Experiences', $entry['en']);
        $this->assertNotEmpty($entry['ar']);
        $this->assertArrayHasKey('home|hero.subheading', TranslationCatalog::options('subheading'));
        $this->assertNotNull(TranslationCatalog::find('*', 'Payment was cancelled. Please try again.'));
    }
}
