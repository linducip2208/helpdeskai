<?php

return [
    'name' => env('HELPDESK_NAME', 'HelpDesk AI'),
    'version' => '1.0.0',

    'ticket_prefix' => env('HELPDESK_TICKET_PREFIX', 'TKT'),
    'default_priority' => env('HELPDESK_DEFAULT_PRIORITY', 'medium'),
    'default_status' => env('HELPDESK_DEFAULT_STATUS', 'open'),

    'sla' => [
        'default_first_response' => env('HELPDESK_SLA_FIRST_RESPONSE', 60),
        'default_resolution' => env('HELPDESK_SLA_RESOLUTION', 480),
    ],

    'ai' => [
        'enabled' => env('HELPDESK_AI_ENABLED', true),
        'features' => [
            'ticket.classify' => 'Smart Ticket Classification',
            'ticket.suggest' => 'AI Response Suggestions',
            'ticket.sentiment' => 'Sentiment Analysis',
            'ticket.summarize' => 'Ticket Summarization',
            'knowledge.answer' => 'Knowledge-Grounded Answers (RAG)',
            'chat.autoreply' => 'Auto Chat Reply',
            'kb.search' => 'AI Knowledge Search',
        ],
    ],

    'chat' => [
        'enabled' => env('HELPDESK_CHAT_ENABLED', true),
        'realtime' => env('HELPDESK_CHAT_REALTIME', true),
    ],

    'knowledge' => [
        'articles_per_page' => env('HELPDESK_KB_PER_PAGE', 10),
    ],

    'pseo' => [
        'enabled' => env('HELPDESK_PSEO_ENABLED', true),
    ],

    'sales_contact' => env('HELPDESK_SALES_CONTACT', '081296052010'),
];
