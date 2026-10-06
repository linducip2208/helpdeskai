<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            AdminUserSeeder::class,
            AgentUserSeeder::class,
            CustomerUserSeeder::class,
            DepartmentSeeder::class,
            CategorySeeder::class,
            SettingSeeder::class,
            EmailTemplateSeeder::class,
            AiPresetSeeder::class,
            AiFeatureConfigSeeder::class,
            KnowledgeBaseSeeder::class,
            ServiceSeeder::class,
            SampleTicketSeeder::class,
        ]);
    }
}
