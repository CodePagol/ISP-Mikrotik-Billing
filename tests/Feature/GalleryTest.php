<?php

namespace Tests\Feature;

use App\Models\MainSiteData;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GalleryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure default settings exist
        MainSiteData::setValue('site_status', 'active');
        MainSiteData::setValue('site_maintenance', '0');
        MainSiteData::setValue('gallery_items', []);
        MainSiteData::setValue('gallery_categories', []);
    }

    public function test_guest_cannot_upload_gallery_image()
    {
        $this->markTestSkipped('Superseded by Livewire actions in MainSiteSetup.');
        $response = $this->post(route('gallery.upload'), [
            'image' => UploadedFile::fake()->image('gallery.jpg'),
            'caption' => 'Test image caption',
            'category' => 'category-1',
        ]);

        $response->assertStatus(403);
    }

    public function test_non_admin_user_cannot_upload_gallery_image()
    {
        $this->markTestSkipped('Superseded by Livewire actions in MainSiteSetup.');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('gallery.upload'), [
            'image' => UploadedFile::fake()->image('gallery.jpg'),
            'caption' => 'Test image caption',
            'category' => 'category-1',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_with_role_can_upload_gallery_image()
    {
        $this->markTestSkipped('Superseded by Livewire actions in MainSiteSetup.');
        Storage::fake('public');

        $role = Role::create(['name' => 'Super Admin']);
        $user = User::factory()->create();
        $user->assignRole($role);

        $response = $this->actingAs($user)->post(route('gallery.upload'), [
            'image' => UploadedFile::fake()->image('photo.jpg'),
            'caption' => 'Scenic View',
            'category' => 'nature',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertStatus(302); // Redirect back
        dump('Global Session contents:', session()->all());

        dump('Gallery items in DB:', MainSiteData::getValue('gallery_items', []));

        $response->assertSessionHas('success', 'Image uploaded successfully.');

        // Assert data is stored in MainSiteData
        $items = MainSiteData::getValue('gallery_items', []);
        $this->assertCount(1, $items);
        $this->assertEquals('Scenic View', $items[0]['caption']);
        $this->assertEquals('nature', $items[0]['category']);

        // Assert file exists in fake storage
        Storage::disk('public')->assertExists($items[0]['image']);

        // Assert category is added to categories list
        $cats = MainSiteData::getValue('gallery_categories', []);
        $this->assertCount(1, $cats);
        $this->assertEquals('nature', $cats[0]['key']);
        $this->assertEquals('Nature', $cats[0]['label']);
    }

    public function test_admin_can_delete_gallery_image()
    {
        $this->markTestSkipped('Superseded by Livewire actions in MainSiteSetup.');
        Storage::fake('public');

        // Setup some fake data in the gallery items
        $fakeImagePath = 'gallery/fake-image.jpg';
        Storage::disk('public')->put($fakeImagePath, 'fake-file-content');

        MainSiteData::setValue('gallery_items', [
            [
                'image' => $fakeImagePath,
                'caption' => 'Temporary Caption',
                'category' => 'nature',
            ],
        ]);

        $role = Role::create(['name' => 'Super Admin']);
        $user = User::factory()->create();
        $user->assignRole($role);

        // Verify the image exists before deletion
        Storage::disk('public')->assertExists($fakeImagePath);

        // Delete the image
        $response = $this->actingAs($user)->delete(route('gallery.delete', 0));

        $response->assertStatus(302);
        $response->assertSessionHas('success', 'Image deleted successfully.');

        // Assert that the image file was deleted from storage
        Storage::disk('public')->assertMissing($fakeImagePath);

        // Assert that the item was removed from MainSiteData
        $items = MainSiteData::getValue('gallery_items', []);
        $this->assertCount(0, $items);
    }
}
