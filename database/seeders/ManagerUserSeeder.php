<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ManagerUserSeeder extends Seeder
{
    public function run(): void
    {
        $manager = User::firstOrCreate(
            ['email' => 'manager@helpdesk.test'],
            [
                'name' => 'Manager',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        if (! $manager->hasRole('manager')) {
            $manager->assignRole('manager');
        }
    }
}
