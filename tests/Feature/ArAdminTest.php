<?php

namespace Tests\Feature;

use App\Filament\Resources\ArDestinationResource\Pages\EditArDestination;
use App\Filament\Resources\ArDestinationResource\Pages\ListArDestinations;
use App\Filament\Resources\ArDestinationResource\RelationManagers\ZonesRelationManager;
use App\Filament\Resources\ArItemResource\Pages\EditArItem;
use App\Filament\Resources\ArItemResource\Pages\ListArItems;
use App\Models\ArItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ArAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->withoutVite();

        $role = Role::findOrCreate('super_admin', 'web');
        foreach (['ArDestination', 'ArItem'] as $model) {
            foreach (['ViewAny', 'View', 'Create', 'Update', 'Delete', 'DeleteAny'] as $ability) {
                $role->givePermissionTo(Permission::findOrCreate("{$ability}:{$model}", 'web'));
            }
        }

        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole($role);
        $this->actingAs($admin);
    }

    public function test_admin_pages_render(): void
    {
        $item = ArItem::factory()->create();
        $item->addMedia(UploadedFile::fake()->image('t.jpg'))->toMediaCollection('target');
        $destination = $item->zone->destination;

        Livewire::test(ListArDestinations::class)->assertOk()->assertCanSeeTableRecords([$destination]);
        Livewire::test(ListArItems::class)->assertOk()->assertCanSeeTableRecords([$item]);
        Livewire::test(EditArDestination::class, ['record' => $destination->getRouteKey()])->assertOk();

        Livewire::test(ZonesRelationManager::class, ['ownerRecord' => $destination, 'pageClass' => EditArDestination::class])
            ->assertOk()
            ->assertCanSeeTableRecords([$item->zone])
            ->mountTableAction('compile', $item->zone)
            ->assertMountedActionModalSeeHtml('arCompiler(')
            ->assertMountedActionModalSee(__('explore.compile.start'));
    }

    public function test_saving_one_language_narration_keeps_the_others(): void
    {
        $item = ArItem::factory()->create();
        $item->addMedia(UploadedFile::fake()->create('ar.mp3', 10, 'audio/mpeg'))
            ->withCustomProperties(['locale' => 'ar'])->toMediaCollection('audio');

        Livewire::test(EditArItem::class, ['record' => $item->getRouteKey()])
            ->fillForm(['audio_en' => [UploadedFile::fake()->create('en.mp3', 10, 'audio/mpeg')]])
            ->call('save')
            ->assertHasNoFormErrors();

        $item->refresh();
        $this->assertStringContainsString('ar.mp3', $item->audioUrl('ar'));
        $this->assertNotNull($item->audioUrl('en'));
        $this->assertCount(2, $item->getMedia('audio'));
    }
}
