<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'create-user-role',
            'edit-user-role',
            'delete-user-role',
            'create-user',
            'edit-user',
            'delete-user',
            'view-user',
            'create-customer',
            'edit-customer',
            'delete-customer',
            'view-customer',
            'enable-customer',
            'disable-customer',
            'inactive-customer',
            'free-customer',
            'pending-customer',
            'collection-customer',
            'customer-billing-info',
            'customer-server-info',
            'customer-official-info',
            'password-reset',
            'payment-collection',
            'payment-edit',
            'payment-delete',
            'payment-history',
            'address-setup',
            'edit-address',
            'delete-address',
            'address-order',
            'mikrotik-setup',
            'edit-mikrotik',
            'delete-mikrotik',
            'mikrotik-user-create',
            'mikrotik-user-edit',
            'mikrotik-user-delete',
            'mikrotik-connection',
            'package-setup',
            'edit-package',
            'delete-package',
            'sms-setup',
            'edit-sms',
            'delete-sms',
            'create-web-content',
            'edit-web-content',
            'delete-web-content',
            'site-settings',
            'create-product',
            'edit-product',
            'delete-product',
            'search-customer',
            'all-customer',
            'collection-list',
            'without-collection-list',
            'enable-pending-customer',
            'recent-customer',
            'update-bill',
            'amount-collection',
            'amount-collection-report',
            'amount-collection-edit',
            'complain-list',
            'print-setup',
            'payment-setup',
            'mikrotik-auto-backup',
        ];

        // Looping and Inserting Array's Permissions into Permission Table
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
    }
}
