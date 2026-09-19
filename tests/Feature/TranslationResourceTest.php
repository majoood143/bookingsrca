<?php

namespace Tests\Feature;

use App\Filament\Resources\TranslationResource\Pages\CreateTranslation;
use App\Filament\Resources\TranslationResource\Pages\EditTranslation;
use App\Filament\Resources\TranslationResource\Pages\ListTranslations;
use App\Models\Translation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TranslationResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Stands in for `php artisan shield:generate`, which creates these permissions in a real deployment.
        $role = Role::findOrCreate('super_admin', 'web');
        foreach (['ViewAny', 'View', 'Create', 'Update', 'Delete', 'DeleteAny'] as $ability) {
            $role->givePermissionTo(Permission::findOrCreate("{$ability}:Translation", 'web'));
        }

        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole($role);
        $this->actingAs($admin);
    }

    public function test_list_shows_the_seeded_hero_rows(): void
    {
        $rows = Translation::where('group', 'home')->get();

        Livewire::test(ListTranslations::class)
            ->assertOk()
            ->assertCanSeeTableRecords($rows);
    }

    public function test_picking_an_existing_key_prefills_the_current_text(): void
    {
        Livewire::test(CreateTranslation::class)
            ->fillForm(['catalog_key' => 'home|section.heading'])
            ->assertSchemaStateSet([
                'group' => 'home',
                'key' => 'section.heading',
                'text' => [
                    'en' => __('home.section.heading', [], 'en'),
                    'ar' => __('home.section.heading', [], 'ar'),
                ],
            ]);
    }

    public function test_admin_can_create_a_translation_and_it_goes_live(): void
    {
        Livewire::test(CreateTranslation::class)
            ->fillForm([
                'group' => 'home',
                'key' => 'section.heading',
                'text' => ['en' => 'Our Events', 'ar' => 'فعالياتنا'],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame('Our Events', __('home.section.heading', [], 'en'));
        $this->assertSame('فعالياتنا', __('home.section.heading', [], 'ar'));
    }

    public function test_group_and_key_pair_must_be_unique(): void
    {
        Livewire::test(CreateTranslation::class)
            ->fillForm([
                'group' => 'home',
                'key' => 'hero.heading',
                'text' => ['en' => 'Duplicate'],
            ])
            ->call('create')
            ->assertHasFormErrors(['key']);
    }

    public function test_at_least_one_language_is_required(): void
    {
        Livewire::test(CreateTranslation::class)
            ->fillForm(['group' => 'home', 'key' => 'section.heading', 'text' => ['en' => '', 'ar' => '']])
            ->call('create')
            ->assertHasFormErrors(['text.en', 'text.ar']);
    }

    public function test_admin_can_edit_and_reset_the_hero_heading(): void
    {
        $row = Translation::where(['group' => 'home', 'key' => 'hero.heading'])->firstOrFail();

        Livewire::test(EditTranslation::class, ['record' => $row->getKey()])
            ->fillForm(['text' => ['en' => 'Edited EN', 'ar' => 'معدل']])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Edited EN', __('home.hero.heading', [], 'en'));
        $this->assertSame('معدل', __('home.hero.heading', [], 'ar'));

        Livewire::test(ListTranslations::class)->callTableAction('delete', $row);

        $this->assertSame('Discover & Book Amazing Experiences', __('home.hero.heading', [], 'en'));
    }
}
