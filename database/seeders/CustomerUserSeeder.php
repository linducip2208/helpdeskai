<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomerUserSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::firstOrCreate(
            ['email' => 'customer@helpdesk.test'],
            [
                'name' => 'Customer',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        if (! $customer->hasRole('customer')) {
            $customer->assignRole('customer');
        }
    }
}
