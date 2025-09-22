<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        // Define admin credentials
        $adminCredentials = [
            [
                'name' => 'Deep3Prep Admin',
                'email' => 'admin@deep3prep.com',
                'password' => 'Deep3PrepAdmin2024!',
                'is_super_admin' => true
            ],
            [
                'name' => 'System Administrator',
                'email' => 'system@deep3prep.com',
                'password' => 'SystemAdmin2024!',
                'is_super_admin' => true
            ]
        ];

        // Insert or update admin users
        foreach ($adminCredentials as $admin) {
            Admin::updateOrCreate(
                ['email' => $admin['email']],
                [
                    'name' => $admin['name'],
                    'password' => Hash::make($admin['password']),
                    'is_super_admin' => $admin['is_super_admin']
                ]
            );
        }
    }
}
