<?php

namespace Tests\Feature;

use App\Events\PackagePurchased;
use App\Livewire\EditCustomer;
use App\Livewire\NewCustomer;
use App\Livewire\Reseller\ResellerPackageManagement;
use App\Models\BillingInfo;
use App\Models\CustomersInfo;
use App\Models\PackageList;
use App\Models\Reseller;
use App\Models\User;
use App\Models\Voucher;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ResellerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Prevent layout image helpers from querying main_site_data table or mock it
        // Since main_site_data might not exist in testing sqlite without migrations,
        // we make sure all migrations are run. RefreshDatabase runs migrations automatically.
    }

    /** @test */
    public function it_can_create_a_reseller_profile()
    {
        $role = Role::create(['name' => 'Reseller']);
        $user = User::create([
            'name' => 'John Reseller',
            'email' => 'john@reseller.com',
            'mobile' => '01711122233',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole($role);

        $reseller = Reseller::create([
            'user_id' => $user->id,
            'company' => 'Reseller Corp',
            'commission_percentage' => 20.00,
            'balance' => 100.00,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('resellers', [
            'id' => $reseller->id,
            'company' => 'Reseller Corp',
            'commission_percentage' => 20.00,
            'balance' => 100.00,
        ]);

        $this->assertTrue($reseller->isActive());
    }

    /** @test */
    public function it_redirects_reseller_user_from_admin_dashboard_to_reseller_dashboard()
    {
        $role = Role::create(['name' => 'Reseller']);
        $user = User::create([
            'name' => 'John Reseller',
            'email' => 'john@reseller.com',
            'mobile' => '01711122233',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole($role);

        $reseller = Reseller::create([
            'user_id' => $user->id,
            'company' => 'Reseller Corp',
            'commission_percentage' => 20.00,
            'balance' => 100.00,
            'status' => 'active',
        ]);

        $baseDomain = parse_url(config('app.url'), PHP_URL_HOST) ?: 'localhost';
        $response = $this->actingAs($user)
            ->withHeaders(['Host' => 'billing.'.$baseDomain])
            ->get(route('dashboard'));

        $response->assertRedirect(route('reseller.dashboard'));
    }

    /** @test */
    public function it_awards_commission_to_reseller_on_package_purchased_event()
    {
        // Setup Reseller
        $role = Role::create(['name' => 'Reseller']);
        $user = User::create([
            'name' => 'John Reseller',
            'email' => 'john@reseller.com',
            'mobile' => '01711122233',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole($role);

        $reseller = Reseller::create([
            'user_id' => $user->id,
            'company' => 'Reseller Corp',
            'commission_percentage' => 10.00, // 10% commission
            'balance' => 0.00,
            'status' => 'active',
        ]);

        // Setup Package
        $package = PackageList::create([
            'package' => 'Premium_10M',
            'price' => 1000.00,
            'speed' => '10 Mbps',
        ]);

        // Setup Reseller Customer
        $customer = CustomersInfo::create([
            'reseller_id' => $reseller->id,
            'customer_unique_id' => 'CUST001',
            'customer_name' => 'Bob Customer',
            'mobile' => '88017000000',
            'package_id' => $package->id,
            'status' => 'active',
        ]);

        // Fire event
        event(new PackagePurchased($customer, 1000.00));

        // Assert reseller commission record is created
        $this->assertDatabaseHas('reseller_commissions', [
            'reseller_id' => $reseller->id,
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'amount' => 100.00, // 10% of 1000
            'commission_percentage' => 10.00,
        ]);

        // Assert reseller balance is updated
        $reseller->refresh();
        $this->assertEquals(100.00, (float) $reseller->balance);

        // Assert wallet transaction is recorded
        $this->assertDatabaseHas('reseller_wallet_transactions', [
            'reseller_id' => $reseller->id,
            'type' => 'credit',
            'amount' => 100.00,
            'reference_type' => 'commission',
        ]);
    }

    /** @test */
    public function it_can_generate_and_redeem_fixed_amount_voucher()
    {
        // Setup Reseller
        $role = Role::create(['name' => 'Reseller']);
        $user = User::create([
            'name' => 'John Reseller',
            'email' => 'john@reseller.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole($role);

        $reseller = Reseller::create([
            'user_id' => $user->id,
            'commission_percentage' => 10.00,
            'balance' => 0.00,
            'status' => 'active',
        ]);

        // Setup Customer
        $customer = CustomersInfo::create([
            'reseller_id' => $reseller->id,
            'customer_unique_id' => 'CUST002',
            'customer_name' => 'Alice Customer',
            'mobile' => '88017000001',
            'status' => 'pending',
        ]);

        // Setup BillingInfo for Customer
        $billing = BillingInfo::create([
            'customer_bill_unique_id' => $customer->customer_unique_id,
            'billing_type' => 'prepaid',
            'monthly_rent' => 500.00,
            'due_amount' => 500.00,
            'total_amount' => 500.00,
        ]);

        // Create Voucher
        $voucher = Voucher::create([
            'code' => 'VCH-TEST-1234',
            'value' => 500.00,
            'type' => 'fixed_amount',
            'status' => 'unused',
            'expiry_date' => now()->addDays(10),
            'reseller_id' => $reseller->id,
        ]);

        // Mock payment service to skip Mikrotik API router connections
        $paymentServiceMock = $this->mock(PaymentService::class, function ($mock) {
            $mock->shouldReceive('processSuccessPayment')
                ->once()
                ->with(\Mockery::type(CustomersInfo::class), 500.00, 'voucher', 'VCH-TEST-1234')
                ->andReturn(true);
        });

        // Act - Redeem via post request simulation or direct call
        $baseDomain = parse_url(config('app.url'), PHP_URL_HOST) ?: 'localhost';
        $response = $this->withHeaders(['Host' => 'portal.'.$baseDomain])
            ->post(route('portal.voucher.redeem'), [
                'username' => 'CUST002',
                'code' => 'VCH-TEST-1234',
            ]);

        $response->assertRedirect();

        // Assert voucher status updated to used
        $voucher->refresh();
        $this->assertEquals('used', $voucher->status);
        $this->assertEquals($customer->id, $voucher->used_by_customer_id);
        $this->assertNotNull($voucher->used_at);
    }

    /** @test */
    public function it_allows_reseller_to_create_customer_via_new_customer_component()
    {
        $role = Role::create(['name' => 'Reseller']);
        $user = User::create([
            'name' => 'John Reseller',
            'email' => 'john@reseller.com',
            'mobile' => '01711122233',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole($role);

        $reseller = Reseller::create([
            'user_id' => $user->id,
            'company' => 'Reseller Corp',
            'commission_percentage' => 10.00,
            'balance' => 0.00,
            'status' => 'active',
            'phone' => '01711122233',
        ]);

        $package = PackageList::create([
            'package' => 'ResellerPackage',
            'price' => 500.00,
            'speed' => '5 Mbps',
            'reseller_id' => $reseller->id,
        ]);

        $this->actingAs($user);

        Livewire::test(NewCustomer::class)
            ->set('customer_name', 'Reseller Customer')
            ->set('mobile', '01711111111')
            ->set('package_name', 'ResellerPackage')
            ->set('monthly_rent', 500)
            ->set('billing_type', 'prepaid')
            ->set('connection_type', 'fiber')
            ->set('connectivity_type', 'shared')
            ->set('connected_by', $user->id)
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('reseller.customers.index'));

        $this->assertDatabaseHas('customers_infos', [
            'customer_name' => 'Reseller Customer',
            'mobile' => '8801711111111',
            'reseller_id' => $reseller->id,
            'status' => 'pending',
            'package_id' => $package->id,
        ]);
    }

    /** @test */
    public function it_allows_reseller_to_edit_their_own_customer_via_edit_customer_component()
    {
        $role = Role::create(['name' => 'Reseller']);
        $user = User::create([
            'name' => 'John Reseller',
            'email' => 'john@reseller.com',
            'mobile' => '01711122233',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole($role);

        $reseller = Reseller::create([
            'user_id' => $user->id,
            'company' => 'Reseller Corp',
            'commission_percentage' => 10.00,
            'balance' => 0.00,
            'status' => 'active',
            'phone' => '01711122233',
        ]);

        $customer = CustomersInfo::create([
            'reseller_id' => $reseller->id,
            'customer_unique_id' => 'RCUST001',
            'customer_name' => 'Reseller Customer',
            'mobile' => '8801711111111',
            'status' => 'pending',
        ]);

        // Setup BillingInfo for Customer
        BillingInfo::create([
            'customer_bill_unique_id' => $customer->customer_unique_id,
            'billing_type' => 'prepaid',
            'monthly_rent' => 500.00,
            'due_amount' => 500.00,
            'total_amount' => 500.00,
        ]);

        $this->actingAs($user);

        Livewire::test(EditCustomer::class, ['customerId' => encrypt($customer->customer_unique_id)])
            ->call('updateCustomer', 'customer_name', 'Updated Reseller Customer Name');

        $this->assertDatabaseHas('customers_infos', [
            'customer_unique_id' => 'RCUST001',
            'customer_name' => 'Updated Reseller Customer Name',
        ]);
    }

    /** @test */
    public function it_prevents_reseller_from_editing_other_reseller_customer()
    {
        $role = Role::create(['name' => 'Reseller']);
        $user = User::create([
            'name' => 'John Reseller',
            'email' => 'john@reseller.com',
            'mobile' => '01711122233',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole($role);

        $reseller = Reseller::create([
            'user_id' => $user->id,
            'company' => 'Reseller Corp',
            'commission_percentage' => 10.00,
            'balance' => 0.00,
            'status' => 'active',
            'phone' => '01711122233',
        ]);

        $otherUser = User::create(['name' => 'Other', 'email' => 'other@reseller.com', 'mobile' => '01711122244', 'password' => bcrypt('password')]);
        $otherUser->assignRole($role);

        $otherReseller = Reseller::create([
            'user_id' => $otherUser->id,
            'company' => 'Other Corp',
            'commission_percentage' => 10.00,
            'balance' => 0.00,
            'status' => 'active',
            'phone' => '01711122244',
        ]);

        $customer = CustomersInfo::create([
            'reseller_id' => $otherReseller->id,
            'customer_unique_id' => 'RCUST002',
            'customer_name' => 'Other Reseller Customer',
            'mobile' => '8801711111111',
            'status' => 'pending',
        ]);

        $this->actingAs($user);

        Livewire::test(EditCustomer::class, ['customerId' => encrypt($customer->customer_unique_id)])
            ->assertStatus(403);
    }

    /** @test */
    public function it_allows_reseller_to_manage_packages_via_livewire_component()
    {
        $role = Role::create(['name' => 'Reseller']);
        $user = User::create([
            'name' => 'John Reseller',
            'email' => 'john@reseller.com',
            'mobile' => '01711122233',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole($role);

        $reseller = Reseller::create([
            'user_id' => $user->id,
            'company' => 'Reseller Corp',
            'commission_percentage' => 10.00,
            'balance' => 0.00,
            'status' => 'active',
            'phone' => '01711122233',
        ]);

        $this->actingAs($user);

        // Test creation
        Livewire::test(ResellerPackageManagement::class)
            ->set('package', 'SuperResellerPack')
            ->set('price', 600.00)
            ->set('speed', '10 Mbps')
            ->set('features_text', "Unlimited\nFiber")
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('package_lists', [
            'package' => 'SuperResellerPack',
            'price' => 600.00,
            'speed' => '10 Mbps',
            'plan_label' => 'John Reseller + SuperResellerPack',
            'reseller_id' => $reseller->id,
        ]);

        $pkg = PackageList::where('package', 'SuperResellerPack')->first();

        // Test editing
        Livewire::test(ResellerPackageManagement::class)
            ->call('edit', $pkg->id)
            ->assertSet('package', 'SuperResellerPack')
            ->assertSet('price', 600.00)
            ->set('price', 650.00)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('package_lists', [
            'id' => $pkg->id,
            'price' => 650.00,
            'plan_label' => 'John Reseller + SuperResellerPack',
        ]);

        // Test deletion
        Livewire::test(ResellerPackageManagement::class)
            ->call('delete', $pkg->id);

        $this->assertDatabaseMissing('package_lists', [
            'id' => $pkg->id,
        ]);
    }

    /** @test */
    public function it_displays_reseller_data_on_admin_dashboard()
    {
        $adminRole = Role::create(['name' => 'Super Admin']);
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@isp.com',
            'mobile' => '01711122299',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole($adminRole);

        $role = Role::create(['name' => 'Reseller']);
        $user = User::create([
            'name' => 'John Reseller',
            'email' => 'john@reseller.com',
            'mobile' => '01711122233',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole($role);

        $reseller = Reseller::create([
            'user_id' => $user->id,
            'company' => 'Reseller Corp',
            'commission_percentage' => 10.00,
            'balance' => 350.00,
            'status' => 'active',
            'phone' => '01711122233',
        ]);

        $baseDomain = parse_url(config('app.url'), PHP_URL_HOST) ?: 'localhost';
        $response = $this->actingAs($admin)
            ->withHeaders(['Host' => 'billing.'.$baseDomain])
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Total Resellers');
        $response->assertSee('Reseller Customers');
        $response->assertSee('Wallet Balances');
        $response->assertSee('Total Commissions');
    }

    /** @test */
    public function it_filters_reseller_customers_on_admin_customer_list_datasource()
    {
        $adminRole = Role::create(['name' => 'Super Admin']);
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@isp.com',
            'mobile' => '01711122299',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole($adminRole);

        $role = Role::create(['name' => 'Reseller']);
        $user1 = User::create([
            'name' => 'John Reseller',
            'email' => 'john@reseller.com',
            'mobile' => '01711122233',
            'password' => bcrypt('password'),
        ]);
        $user1->assignRole($role);

        $reseller1 = Reseller::create([
            'user_id' => $user1->id,
            'company' => 'Reseller One Corp',
            'commission_percentage' => 10.00,
            'balance' => 0.00,
            'status' => 'active',
            'phone' => '01711122233',
        ]);

        $user2 = User::create([
            'name' => 'Bob Reseller',
            'email' => 'bob@reseller.com',
            'mobile' => '01711122244',
            'password' => bcrypt('password'),
        ]);
        $user2->assignRole($role);

        $reseller2 = Reseller::create([
            'user_id' => $user2->id,
            'company' => 'Reseller Two Corp',
            'commission_percentage' => 10.00,
            'balance' => 0.00,
            'status' => 'active',
            'phone' => '01711122244',
        ]);

        // Create reseller 1 customer
        $customer1 = CustomersInfo::create([
            'reseller_id' => $reseller1->id,
            'customer_unique_id' => 'RCUST001',
            'customer_name' => 'Reseller One Customer',
            'mobile' => '8801700000001',
            'status' => 'active',
        ]);

        // Create reseller 2 customer
        $customer2 = CustomersInfo::create([
            'reseller_id' => $reseller2->id,
            'customer_unique_id' => 'RCUST002',
            'customer_name' => 'Reseller Two Customer',
            'mobile' => '8801700000002',
            'status' => 'active',
        ]);

        // Create a regular customer
        $regularCustomer = CustomersInfo::create([
            'customer_unique_id' => 'ACUST001',
            'customer_name' => 'Regular Customer One',
            'mobile' => '8801700000003',
            'status' => 'active',
        ]);

        $baseDomain = parse_url(config('app.url'), PHP_URL_HOST) ?: 'localhost';

        // Query with filter = reseller and reseller_id = reseller1->id
        $response = $this->actingAs($admin)
            ->withHeaders(['Host' => 'billing.'.$baseDomain])
            ->get(route('customers.data', ['filter' => 'reseller', 'reseller_id' => $reseller1->id]));

        $response->assertStatus(200);
        $json = $response->json();

        $this->assertArrayHasKey('data', $json);
        $names = collect($json['data'])->pluck('customer_name')->toArray();

        // Assert that reseller 1's customer is in the list
        $this->assertTrue(in_array('Reseller One Customer', $names));
        // Assert that reseller 2's customer is NOT in the list
        $this->assertFalse(in_array('Reseller Two Customer', $names));
        // Assert that the regular customer is NOT in the list
        $this->assertFalse(in_array('Regular Customer One', $names));
    }

    /** @test */
    public function it_filters_customers_on_reseller_customer_list_datasource_for_authenticated_reseller()
    {
        $permission = \Spatie\Permission\Models\Permission::create(['name' => 'view-customer']);
        $role = Role::create(['name' => 'Reseller']);
        $role->givePermissionTo($permission);
        $user1 = User::create([
            'name' => 'Reseller One',
            'email' => 'reseller1@isp.com',
            'mobile' => '01711122233',
            'password' => bcrypt('password'),
        ]);
        $user1->assignRole($role);

        $reseller1 = Reseller::create([
            'user_id' => $user1->id,
            'company' => 'Reseller One Corp',
            'commission_percentage' => 10.00,
            'balance' => 0.00,
            'status' => 'active',
            'phone' => '01711122233',
        ]);

        $user2 = User::create([
            'name' => 'Reseller Two',
            'email' => 'reseller2@isp.com',
            'mobile' => '01711122244',
            'password' => bcrypt('password'),
        ]);
        $user2->assignRole($role);

        $reseller2 = Reseller::create([
            'user_id' => $user2->id,
            'company' => 'Reseller Two Corp',
            'commission_percentage' => 10.00,
            'balance' => 0.00,
            'status' => 'active',
            'phone' => '01711122244',
        ]);

        // Reseller 1 Customer
        CustomersInfo::create([
            'reseller_id' => $reseller1->id,
            'customer_unique_id' => 'R1CUST',
            'customer_name' => 'Reseller 1 Cust',
            'mobile' => '8801700000001',
            'status' => 'active',
        ]);

        // Reseller 2 Customer
        CustomersInfo::create([
            'reseller_id' => $reseller2->id,
            'customer_unique_id' => 'R2CUST',
            'customer_name' => 'Reseller 2 Cust',
            'mobile' => '8801700000002',
            'status' => 'active',
        ]);

        $baseDomain = parse_url(config('app.url'), PHP_URL_HOST) ?: 'localhost';

        // Log in as Reseller 1 and call their customers data route
        $response = $this->actingAs($user1)
            ->withHeaders(['Host' => 'billing.'.$baseDomain])
            ->get(route('reseller.customers.data', ['filter' => 'all']));

        $response->assertStatus(200);
        $json = $response->json();

        $this->assertArrayHasKey('data', $json);
        $names = collect($json['data'])->pluck('customer_name')->toArray();

        // Assert that reseller 1 sees their customer
        $this->assertTrue(in_array('Reseller 1 Cust', $names));
        // Assert that reseller 1 DOES NOT see reseller 2's customer
        $this->assertFalse(in_array('Reseller 2 Cust', $names));
    }

    /** @test */
    public function it_prevents_reseller_from_updating_customer_status_to_free()
    {
        $role = Role::create(['name' => 'Reseller']);
        $user = User::create([
            'name' => 'John Reseller',
            'email' => 'john@reseller.com',
            'mobile' => '01711122233',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole($role);

        $reseller = Reseller::create([
            'user_id' => $user->id,
            'company' => 'Reseller Corp',
            'commission_percentage' => 10.00,
            'balance' => 0.00,
            'status' => 'active',
            'phone' => '01711122233',
        ]);

        $customer = CustomersInfo::create([
            'reseller_id' => $reseller->id,
            'customer_unique_id' => 'RCUST999',
            'customer_name' => 'Reseller Customer',
            'mobile' => '8801711111111',
            'status' => 'pending',
        ]);

        BillingInfo::create([
            'customer_bill_unique_id' => $customer->customer_unique_id,
            'billing_type' => 'prepaid',
            'monthly_rent' => 500.00,
            'due_amount' => 500.00,
            'total_amount' => 500.00,
        ]);

        $this->actingAs($user);

        Livewire::test(EditCustomer::class, ['customerId' => encrypt($customer->customer_unique_id)])
            ->call('updateCustomer', 'official.status', 'free');

        // Assert that the database status remains pending and has NOT changed to free
        $this->assertDatabaseHas('customers_infos', [
            'customer_unique_id' => 'RCUST999',
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function it_filters_reseller_collections_and_commissions_month_wise_on_index_page()
    {
        $adminRole = Role::create(['name' => 'Super Admin']);
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@isp.com',
            'mobile' => '01711122299',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole($adminRole);

        $role = Role::create(['name' => 'Reseller']);
        $user = User::create([
            'name' => 'John Reseller',
            'email' => 'john@reseller.com',
            'mobile' => '01711122233',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole($role);

        $reseller = Reseller::create([
            'user_id' => $user->id,
            'company' => 'Reseller Corp',
            'commission_percentage' => 10.00,
            'balance' => 0.00,
            'status' => 'active',
            'phone' => '01711122233',
        ]);

        $package = PackageList::create([
            'package' => 'ResellerPackage',
            'price' => 500.00,
            'speed' => '5 Mbps',
        ]);

        $customer = CustomersInfo::create([
            'reseller_id' => $reseller->id,
            'customer_unique_id' => 'RCUST001',
            'customer_name' => 'Reseller Customer',
            'mobile' => '8801711111111',
            'status' => 'active',
            'package_id' => $package->id,
        ]);

        // Current Month (commission and collection)
        \App\Models\ResellerCommission::create([
            'reseller_id' => $reseller->id,
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'amount' => 100.00,
            'commission_percentage' => 10.00,
            'created_at' => now(),
        ]);

        \App\Models\CollectionSummary::create([
            'customer_collection_unique_id' => $customer->customer_unique_id,
            'collection_date' => now(),
            'collection_amount' => 500.00,
            'payment_status' => 'paid',
            'invoice_no' => 'INV-001',
        ]);

        // Last Month (commission and collection)
        \App\Models\ResellerCommission::create([
            'reseller_id' => $reseller->id,
            'customer_id' => $customer->id,
            'package_id' => $package->id,
            'amount' => 150.00,
            'commission_percentage' => 15.00,
            'created_at' => now()->subMonth()->startOfMonth(),
        ]);

        \App\Models\CollectionSummary::create([
            'customer_collection_unique_id' => $customer->customer_unique_id,
            'collection_date' => now()->subMonth()->startOfMonth(),
            'collection_amount' => 700.00,
            'payment_status' => 'paid',
            'invoice_no' => 'INV-002',
        ]);

        $baseDomain = parse_url(config('app.url'), PHP_URL_HOST) ?: 'localhost';

        // 1. Check default month (current month)
        $response = $this->actingAs($admin)
            ->withHeaders(['Host' => 'billing.'.$baseDomain])
            ->get(route('admin.resellers.index'));

        $response->assertStatus(200);
        $resellerObj = $response->viewData('resellers')->firstWhere('id', $reseller->id);
        $this->assertEquals(500.00, (float) $resellerObj->totalCollections());
        $this->assertEquals(100.00, (float) $resellerObj->totalProfit());

        // 2. Check all time
        $response = $this->actingAs($admin)
            ->withHeaders(['Host' => 'billing.'.$baseDomain])
            ->get(route('admin.resellers.index', ['month' => 'all']));

        $response->assertStatus(200);
        $resellerObj = $response->viewData('resellers')->firstWhere('id', $reseller->id);
        $this->assertEquals(1200.00, (float) $resellerObj->totalCollections());
        $this->assertEquals(250.00, (float) $resellerObj->totalProfit());

        // 3. Check specific month (last month)
        $lastMonth = now()->subMonth();
        $response = $this->actingAs($admin)
            ->withHeaders(['Host' => 'billing.'.$baseDomain])
            ->get(route('admin.resellers.index', ['month' => $lastMonth->month, 'year' => $lastMonth->year]));

        $response->assertStatus(200);
        $resellerObj = $response->viewData('resellers')->firstWhere('id', $reseller->id);
        $this->assertEquals(700.00, (float) $resellerObj->totalCollections());
        $this->assertEquals(150.00, (float) $resellerObj->totalProfit());
    }
}
