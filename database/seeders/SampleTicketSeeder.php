<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Database\Seeder;

class SampleTicketSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'customer@helpdesk.test')->first();
        $agent = User::where('email', 'agent@helpdesk.test')->first();
        $department = Department::where('name', 'Technical Support')->first();

        if (! $user || ! $department) {
            return;
        }

        $categoryNames = ['Account Access', 'Invoices', 'Feedback', 'Software Issues', 'Other'];
        $categories = [];
        foreach ($categoryNames as $name) {
            $categories[$name] = Category::where('name', $name)->first();
        }
        $fallbackCategory = Category::first();

        $tickets = [
            [
                'uid' => Ticket::generateUid(),
                'subject' => 'Cannot access my account',
                'body' => 'I am unable to log into my account. It says invalid credentials even though I am sure my password is correct.',
                'priority' => 'high',
                'status' => 'open',
                'category_name' => 'Account Access',
            ],
            [
                'uid' => Ticket::generateUid(),
                'subject' => 'Billing question about last invoice',
                'body' => 'I was charged twice for the same month. Can someone look into this?',
                'priority' => 'medium',
                'status' => 'in_progress',
                'category_name' => 'Invoices',
            ],
            [
                'uid' => Ticket::generateUid(),
                'subject' => 'Feature request: Dark mode',
                'body' => 'Would love to see a dark mode option in the dashboard. Many users have been asking for it.',
                'priority' => 'low',
                'status' => 'open',
                'category_name' => 'Feedback',
            ],
            [
                'uid' => Ticket::generateUid(),
                'subject' => 'Integration with Slack not working',
                'body' => 'The Slack integration stopped sending notifications after the latest update.',
                'priority' => 'medium',
                'status' => 'open',
                'category_name' => 'Software Issues',
            ],
            [
                'uid' => Ticket::generateUid(),
                'subject' => 'How to export ticket reports?',
                'body' => 'I need to export all tickets from last month for a team review. Is there a way to do this?',
                'priority' => 'low',
                'status' => 'resolved',
                'category_name' => 'Other',
            ],
        ];

        foreach ($tickets as $data) {
            $category = $categories[$data['category_name']] ?? $fallbackCategory;

            $ticket = Ticket::create([
                'uid' => $data['uid'],
                'user_id' => $user->id,
                'department_id' => $department->id,
                'category_id' => $category ? $category->id : null,
                'subject' => $data['subject'],
                'body' => $data['body'],
                'priority' => $data['priority'],
                'status' => $data['status'],
            ]);

            TicketReply::create([
                'ticket_id' => $ticket->id,
                'user_id' => $agent ? $agent->id : $user->id,
                'body' => 'Thank you for reaching out. We are looking into this and will get back to you shortly.',
                'is_internal' => false,
            ]);
        }
    }
}
