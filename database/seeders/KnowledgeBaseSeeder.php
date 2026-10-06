<?php

namespace Database\Seeders;

use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class KnowledgeBaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();

        $categoryNames = ['Getting Started', 'Tickets', 'Admin', 'AI & Automation', 'Chat'];
        $categories = [];
        foreach ($categoryNames as $name) {
            $categories[$name] = KnowledgeCategory::firstOrCreate(
                ['name' => $name],
                ['slug' => Str::slug($name)]
            );
        }

        $articles = [
            [
                'title' => 'Getting Started with HelpDesk AI',
                'slug' => 'getting-started',
                'content' => 'Learn how to set up your account, create your first ticket, and navigate the dashboard.',
                'excerpt' => 'Learn how to set up your account and create your first ticket.',
                'category_name' => 'Getting Started',
                'status' => 'published',
                'is_featured' => true,
            ],
            [
                'title' => 'How to Create a Ticket',
                'slug' => 'how-to-create-ticket',
                'content' => 'Step-by-step guide on creating tickets, setting priority, choosing department, and attaching files.',
                'excerpt' => 'Step-by-step guide on creating tickets and setting priority.',
                'category_name' => 'Tickets',
                'status' => 'published',
            ],
            [
                'title' => 'Understanding SLA Policies',
                'slug' => 'understanding-sla',
                'content' => 'Service Level Agreements define response and resolution time targets for different ticket priorities.',
                'excerpt' => 'Service Level Agreements define response and resolution time targets.',
                'category_name' => 'Admin',
                'status' => 'published',
            ],
            [
                'title' => 'Setting Up AI Features',
                'slug' => 'ai-features-setup',
                'content' => 'Configure AI providers, enable ticket classification, response suggestions, and sentiment analysis.',
                'excerpt' => 'Configure AI providers for smart ticket automation.',
                'category_name' => 'AI & Automation',
                'status' => 'published',
                'is_featured' => true,
            ],
            [
                'title' => 'Live Chat Integration Guide',
                'slug' => 'live-chat-integration',
                'content' => 'Embed the live chat widget on your website and configure real-time messaging.',
                'excerpt' => 'Embed the live chat widget and configure real-time messaging.',
                'category_name' => 'Chat',
                'status' => 'published',
            ],
            [
                'title' => 'FAQ: Common Questions',
                'slug' => 'faq',
                'content' => 'Frequently asked questions about billing, account management, and feature availability.',
                'excerpt' => 'Frequently asked questions about billing and account management.',
                'category_name' => 'Getting Started',
                'status' => 'published',
            ],
        ];

        foreach ($articles as $data) {
            $categoryName = $data['category_name'];
            unset($data['category_name']);

            KnowledgeArticle::firstOrCreate(
                ['slug' => $data['slug']],
                array_merge($data, [
                    'category_id' => $categories[$categoryName]->id,
                    'user_id' => $user ? $user->id : 1,
                ])
            );
        }
    }
}
