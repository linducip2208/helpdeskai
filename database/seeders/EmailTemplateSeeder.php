<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Welcome',
                'key' => 'welcome',
                'subject' => 'Welcome to {{app_name}}',
                'body' => '<p>Hello {{user_name}},</p><p>Welcome to {{app_name}}! We\'re excited to have you on board.</p>',
            ],
            [
                'name' => 'Ticket Created',
                'key' => 'ticket_created',
                'subject' => 'Ticket #{{ticket_id}} has been created',
                'body' => '<p>Hello {{user_name}},</p><p>Your ticket #{{ticket_id}} has been created. We will respond shortly.</p>',
            ],
            [
                'name' => 'Ticket Replied',
                'key' => 'ticket_replied',
                'subject' => 'New reply on ticket #{{ticket_id}}',
                'body' => '<p>Hello {{user_name}},</p><p>There is a new reply on your ticket #{{ticket_id}}.</p>',
            ],
            [
                'name' => 'Ticket Resolved',
                'key' => 'ticket_resolved',
                'subject' => 'Ticket #{{ticket_id}} has been resolved',
                'body' => '<p>Hello {{user_name}},</p><p>Your ticket #{{ticket_id}} has been resolved. If you need further help, feel free to reply.</p>',
            ],
            [
                'name' => 'Ticket Assigned',
                'key' => 'ticket_assigned',
                'subject' => 'Ticket #{{ticket_id}} assigned to you',
                'body' => '<p>Hello {{agent_name}},</p><p>Ticket #{{ticket_id}} has been assigned to you.</p>',
            ],
            [
                'name' => 'SLA Breach Warning',
                'key' => 'sla_breach',
                'subject' => 'SLA breach warning for ticket #{{ticket_id}}',
                'body' => '<p>Alert: Ticket #{{ticket_id}} is approaching SLA breach threshold.</p>',
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::firstOrCreate(
                ['key' => $template['key']],
                $template
            );
        }
    }
}
