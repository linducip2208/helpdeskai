<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AgentUserSeeder extends Seeder
{
    public function run(): void
    {
        $agent = User::firstOrCreate(
            ['email' => 'agent@helpdesk.test'],
            [
                'name' => 'Agent',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        if (! $agent->hasRole('agent')) {
            $agent->assignRole('agent');
        }
    }
}
