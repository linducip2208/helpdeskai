<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'title' => 'AI Ticket Classification',
                'slug' => 'ai-ticket-classification',
                'description' => 'Automatically classify and route incoming tickets using AI.',
                'icon' => 'bot',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Smart Response Suggestions',
                'slug' => 'smart-response-suggestions',
                'description' => 'Get AI-powered response suggestions based on ticket context.',
                'icon' => 'sparkles',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'Sentiment Analysis',
                'slug' => 'sentiment-analysis',
                'description' => 'Detect customer sentiment in real-time to prioritize urgent issues.',
                'icon' => 'heart-pulse',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'title' => 'Live Chat Widget',
                'slug' => 'live-chat-widget',
                'description' => 'Embeddable real-time chat widget with AI auto-reply capabilities.',
                'icon' => 'message-circle',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'title' => 'Knowledge Base',
                'slug' => 'knowledge-base',
                'description' => 'Self-service knowledge base with AI-powered search.',
                'icon' => 'book-open',
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($services as $data) {
            Service::firstOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }
    }
}
