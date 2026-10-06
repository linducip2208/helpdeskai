<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'app_name', 'value' => 'HelpDesk AI', 'type' => 'text', 'group' => 'general'],
            ['key' => 'app_description', 'value' => 'AI-powered customer support system', 'type' => 'textarea', 'group' => 'general'],
            ['key' => 'app_logo', 'value' => null, 'type' => 'image', 'group' => 'general'],
            ['key' => 'primary_color', 'value' => '#4F46E5', 'type' => 'color', 'group' => 'appearance'],
            ['key' => 'mail_mailer', 'value' => env('MAIL_MAILER', 'log'), 'type' => 'text', 'group' => 'mail'],
            ['key' => 'mail_host', 'value' => env('MAIL_HOST', '127.0.0.1'), 'type' => 'text', 'group' => 'mail'],
            ['key' => 'mail_port', 'value' => env('MAIL_PORT', '2525'), 'type' => 'number', 'group' => 'mail'],
            ['key' => 'mail_username', 'value' => null, 'type' => 'text', 'group' => 'mail'],
            ['key' => 'mail_from_address', 'value' => 'hello@helpdeskai.test', 'type' => 'email', 'group' => 'mail'],
            ['key' => 'mail_from_name', 'value' => 'HelpDesk AI', 'type' => 'text', 'group' => 'mail'],
            ['key' => 'seo_title', 'value' => 'HelpDesk AI - Intelligent Customer Support', 'type' => 'text', 'group' => 'seo'],
            ['key' => 'seo_description', 'value' => 'AI-powered customer support system with smart ticket classification, response suggestions, and predictive analytics.', 'type' => 'textarea', 'group' => 'seo'],
            ['key' => 'seo_keywords', 'value' => 'helpdesk, ticketing, customer support, AI', 'type' => 'text', 'group' => 'seo'],
            ['key' => 'chat_enabled', 'value' => 'true', 'type' => 'boolean', 'group' => 'chat'],
            ['key' => 'ai_enabled', 'value' => 'true', 'type' => 'boolean', 'group' => 'ai'],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
