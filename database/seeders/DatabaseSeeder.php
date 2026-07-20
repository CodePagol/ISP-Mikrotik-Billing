<?php

namespace Database\Seeders;

use App\Models\MainSiteData;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\RouterList;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        MainSiteData::setValue('site_name', 'Code Pagol');
        MainSiteData::setValue('site_title', 'Code Pagol');
        MainSiteData::setValue('theme_preset', 'neo');
        MainSiteData::setValue('theme_name', 'midnight_purple');
        MainSiteData::setValue('theme_primary_color', '#6366f1');
        MainSiteData::setValue('theme_accent_color', '#06b6d4');
        MainSiteData::setValue('theme_card_style', 'glass');
        MainSiteData::setValue('theme_border_radius', '20px');
        // User::factory(10)->withPersonalTeam()->create();
        RouterList::create([
            'router_name' => 'Test Router',
            'ip_address' => '127.0.0.1',
            'username' => 'root',
            'password' => '1234',
            'action' => 'connected',
            'ssh_port' => '22',
        ]);
        User::factory()->withPersonalTeam()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            SuperAdminSeeder::class,
            ResellerModuleSeeder::class,
            SmsTemplateSeeder::class,
            // DefaultSettingsTableSeeder::class,
            // ProductSeeder::class,
        ]);
    }
}
