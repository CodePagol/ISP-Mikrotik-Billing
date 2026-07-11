<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Creating Super Admin User
        $superAdmin = User::create([
            'name' => 'Rohan9222',
            'email' => 'rohan9222@gmail.com',
            'password' => Hash::make('rohan9222@gmail.com'),
        ]);
        $superAdmin->assignRole('Super Admin');
    }
}
