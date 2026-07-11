<?php

namespace Tests\Feature;

use App\Models\PackagePurchaseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PackagePurchaseRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function customers_can_submit_purchase_request_via_livewire_form()
    {
        Livewire::test('⚡package-purchase-form')
            ->call('openModal', 'Super Broadband 50 Mbps', 1200)
            ->assertSet('packageName', 'Super Broadband 50 Mbps')
            ->assertSet('price', 1200)
            ->assertSet('showModal', true)
            ->set('name', 'John Doe')
            ->set('phone', '01711122233')
            ->set('email', 'john@example.com')
            ->set('address', 'House 12, Road 5, Sector 3, Uttara, Dhaka')
            ->set('notes', 'Please install as soon as possible.')
            ->call('submitRequest')
            ->assertHasNoErrors()
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('package_purchase_requests', [
            'name' => 'John Doe',
            'phone' => '01711122233',
            'email' => 'john@example.com',
            'package_name' => 'Super Broadband 50 Mbps',
            'price' => 1200,
            'status' => 'pending',
            'notes' => 'Please install as soon as possible.',
        ]);
    }

    /** @test */
    public function purchase_request_form_validates_required_fields()
    {
        Livewire::test('⚡package-purchase-form')
            ->call('openModal', 'Standard 10 Mbps', 650)
            ->set('name', '')
            ->set('phone', '')
            ->set('address', '')
            ->call('submitRequest')
            ->assertHasErrors(['name' => 'required', 'phone' => 'required', 'address' => 'required']);

        $this->assertDatabaseEmpty('package_purchase_requests');
    }

    /** @test */
    public function admin_can_manage_purchase_requests()
    {
        $role = Role::create(['name' => 'Super Admin']);
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@isp.com',
            'mobile' => '01711122299',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole($role);

        $request = PackagePurchaseRequest::create([
            'name' => 'John Customer',
            'phone' => '01888222333',
            'email' => 'customer@isp.com',
            'address' => 'Mirpur, Dhaka',
            'package_name' => '10 Mbps',
            'price' => 650,
            'status' => 'pending',
        ]);

        $this->actingAs($admin);

        // Test list component rendering, filtering, search
        Livewire::test(\App\Livewire\Admin\ManagePurchaseRequests::class)
            ->assertSee('John Customer')
            ->assertSee('10 Mbps')
            ->set('search', 'Different Name')
            ->assertDontSee('John Customer')
            ->set('search', '')
            ->assertSee('John Customer')
            ->set('statusFilter', 'completed')
            ->assertDontSee('John Customer')
            ->set('statusFilter', 'pending')
            ->assertSee('John Customer')
            // Test change status directly
            ->call('changeStatus', $request->id, 'contacted')
            ->assertHasNoErrors();

        $this->assertEquals('contacted', $request->fresh()->status);

        // Test open modal and edit details/status
        Livewire::test(\App\Livewire\Admin\ManagePurchaseRequests::class)
            ->call('openDetailModal', $request->id)
            ->assertSet('selectedRequestId', $request->id)
            ->assertSet('selectedRequestStatus', 'contacted')
            ->set('selectedRequestNotes', 'Updated admin remark')
            ->set('selectedRequestStatus', 'completed')
            ->call('saveDetails')
            ->assertHasNoErrors();

        $this->assertEquals('completed', $request->fresh()->status);
        $this->assertEquals('Updated admin remark', $request->fresh()->notes);

        // Test deletion
        Livewire::test(\App\Livewire\Admin\ManagePurchaseRequests::class)
            ->call('delete', $request->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('package_purchase_requests', ['id' => $request->id]);
    }
}
