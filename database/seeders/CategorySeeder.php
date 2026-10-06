<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Technical Support' => ['Hardware Issues', 'Software Issues', 'Network Problems', 'Account Access'],
            'Billing' => ['Invoices', 'Refunds', 'Payment Methods', 'Subscription'],
            'Sales' => ['Product Inquiry', 'Pricing', 'Demo Request', 'Enterprise'],
            'General Inquiry' => ['Feedback', 'Partnerships', 'Careers', 'Other'],
        ];

        foreach ($categories as $departmentName => $categoryNames) {
            $department = Department::where('name', $departmentName)->first();

            if (! $department) {
                continue;
            }

            foreach ($categoryNames as $name) {
                Category::firstOrCreate(
                    ['name' => $name, 'department_id' => $department->id],
                    [
                        'slug' => Str::slug($name),
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
