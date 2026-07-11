<?php

namespace Database\Seeders;

use App\Models\PackageList;
use App\Models\Reseller;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class ResellerModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create the Reseller Role if it doesn't exist
        $resellerRole = Role::firstOrCreate(['name' => 'Reseller']);

        // 2. Create the Test Reseller User
        $user = User::where('email', 'reseller@isp.com')->first();
        if (!$user) {
            $user = User::create([
                'name' => 'Test Reseller',
                'email' => 'reseller@isp.com',
                'mobile' => '01711122233',
                'password' => bcrypt('password'),
            ]);
        }

        // Assign the role
        if (!$user->hasRole('Reseller')) {
            $user->assignRole($resellerRole);
        }

        // 3. Create the Reseller Profile
        $reseller = Reseller::where('user_id', $user->id)->first();
        if (!$reseller) {
            $reseller = Reseller::create([
                'user_id' => $user->id,
                'company' => 'Reseller ISP Ventures',
                'commission_percentage' => 15.00, // 15% commission
                'balance' => 5000.00, // Initial wallet balance
                'status' => 'active',
                'phone' => '01711122233',
            ]);
        }

        // 4. Map the first 3 admin packages to this reseller (if any packages exist)
        $packages = PackageList::whereNull('reseller_id')->take(3)->pluck('id')->toArray();
        if (!empty($packages)) {
            $reseller->assignedPackages()->sync($packages);
        }
    }
}
