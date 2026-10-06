<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = ['Technical Support', 'Billing', 'Sales', 'General Inquiry'];

        foreach ($departments as $name) {
            Department::firstOrCreate(
                ['name' => $name],
                [
                    'description' => "{$name} department",
                    'is_active' => true,
                ]
            );
        }
    }
}
