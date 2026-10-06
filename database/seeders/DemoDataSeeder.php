<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\Department;
use App\Models\KnowledgeArticle;
use App\Models\KnowledgeCategory;
use App\Models\Post;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding 5000+ demo records...');

        $this->seedDepartmentsAndCategories();
        $customers = $this->seedCustomers(300);
        $agents = $this->seedAgents(40);
        $this->seedServices(40);
        $kbCats = $this->seedKnowledgeCategories();
        $this->seedKnowledgeArticles(150, $kbCats, array_merge($agents, $customers));
        $this->seedPosts(60, $agents);
        $tickets = $this->seedTickets(3500, $customers, $agents);
        $this->seedTicketReplies($tickets, $customers, $agents, 1800);
        $this->seedConversations(150, 1500, $customers, $agents);

        $this->command->info('Done.');
        $this->summary();
    }

    private function seedDepartmentsAndCategories(): void
    {
        $depts = ['Technical Support', 'Billing', 'Sales', 'Onboarding', 'General Inquiries'];
        foreach ($depts as $name) {
            Department::updateOrCreate(
                ['name' => $name],
                ['description' => "Handles {$name}", 'is_active' => true]
            );
        }

        $deptId = Department::value('id');
        $cats = ['Bug Report', 'Feature Request', 'Account Issue', 'Payment', 'Integration', 'How-to Question', 'Other'];
        foreach ($cats as $name) {
            Category::updateOrCreate(
                ['name' => $name],
                ['slug' => Str::slug($name), 'department_id' => $deptId, 'description' => "{$name} category", 'is_active' => true]
            );
        }
    }

    private function seedCustomers(int $n): array
    {
        $existing = User::whereHas('roles', fn ($q) => $q->where('name', 'customer'))->count();
        if ($existing >= $n) {
            return User::whereHas('roles', fn ($q) => $q->where('name', 'customer'))->limit($n + 1)->pluck('id')->all();
        }

        $rows = [];
        $now = now();
        $start = $existing;
        for ($i = $start; $i < $n; $i++) {
            $rows[] = [
                'name' => "Customer {$i}",
                'email' => "customer{$i}@demo.test",
                'password' => Hash::make('password'),
                'is_active' => true,
                'timezone' => 'UTC',
                'role' => 'customer',
                'created_at' => $now->copy()->subDays(rand(1, 365)),
                'updated_at' => $now,
            ];
            if (count($rows) >= 200) {
                User::insert($rows);
                $rows = [];
            }
        }
        if ($rows) {
            User::insert($rows);
        }

        $ids = User::whereLike('email', 'customer%@demo.test')->pluck('id')->all();
        $role = Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);
        DB::table('model_has_roles')->insertOrIgnore(array_map(fn ($id) => [
            'role_id' => $role->id,
            'model_type' => User::class,
            'model_id' => $id,
        ], $ids));

        return $ids;
    }

    private function seedAgents(int $n): array
    {
        $existing = User::whereHas('roles', fn ($q) => $q->where('name', 'agent'))->count();
        if ($existing >= $n) {
            return User::whereHas('roles', fn ($q) => $q->where('name', 'agent'))->limit($n + 1)->pluck('id')->all();
        }

        $rows = [];
        $now = now();
        for ($i = $existing; $i < $n; $i++) {
            $rows[] = [
                'name' => "Agent {$i}",
                'email' => "agent{$i}@demo.test",
                'password' => Hash::make('password'),
                'is_active' => true,
                'timezone' => 'UTC',
                'role' => 'agent',
                'created_at' => $now->copy()->subDays(rand(1, 365)),
                'updated_at' => $now,
            ];
        }
        if ($rows) {
            User::insert($rows);
        }

        $ids = User::whereLike('email', 'agent%@demo.test')->pluck('id')->all();
        $role = Role::firstOrCreate(['name' => 'agent', 'guard_name' => 'web']);
        DB::table('model_has_roles')->insertOrIgnore(array_map(fn ($id) => [
            'role_id' => $role->id,
            'model_type' => User::class,
            'model_id' => $id,
        ], $ids));

        return $ids;
    }

    private function seedServices(int $n): void
    {
        $now = now();
        $rows = [];
        $names = ['Zendesk', 'Freshdesk', 'Intercom', 'Help Scout', 'Front', 'Kayako', 'LiveAgent', 'HappyFox', 'Groove', 'Kustomer', 'Gorgias', 'Tidio', 'Crisp', 'Drift', 'Tawk.to', 'Olark', 'LiveChat', 'Zoho Desk', 'Salesforce Service Cloud', 'ServiceNow', 'Jira Service Management', 'TeamSupport', 'ManageEngine ServiceDesk', 'osTicket', 'OTRS', 'Spiceworks', 'Vivantio', 'TOPdesk', 'Cherwell', 'BMC Helix', 'Hiver', 'Helpshift', 'UserVoice', 'Trengo', 'ChannelReply', 'Re:amaze', 'Userlike', 'Pure Chat', 'JivoChat', 'SnapEngage'];
        for ($i = 0; $i < $n; $i++) {
            $name = $names[$i] ?? ('HelpDesk Tool '.$i);
            $rows[] = [
                'title' => $name,
                'slug' => Str::slug($name.'-'.$i),
                'icon' => null,
                'description' => "Powerful customer support platform {$name}.",
                'content' => "<p>{$name} is a feature-rich helpdesk solution used by thousands of teams worldwide.</p>",
                'is_active' => true,
                'sort_order' => $i,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        Service::insertOrIgnore($rows);
    }

    private function seedKnowledgeCategories(): array
    {
        $now = now();
        $cats = ['Getting Started', 'Account Management', 'Billing & Payments', 'Integrations', 'API & Developer', 'Troubleshooting', 'Security', 'Best Practices'];
        $ids = [];
        foreach ($cats as $i => $name) {
            $cat = KnowledgeCategory::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'description' => "{$name} guides", 'is_active' => true, 'sort_order' => $i, 'updated_at' => $now]
            );
            $ids[] = $cat->id;
        }

        return $ids;
    }

    private function seedKnowledgeArticles(int $n, array $catIds, array $userIds): void
    {
        $now = now();
        $rows = [];
        $titles = [
            'How to reset your password', 'Setting up SSO', 'Configuring webhooks', 'Understanding ticket priorities',
            'Best practices for canned responses', 'Creating SLA policies', 'Custom fields tutorial', 'API authentication guide',
            'Integration with Slack', 'Email piping setup', 'AI auto-reply configuration', 'Sentiment analysis explained',
            'Multi-language support', 'Custom workflow rules', 'Reporting and analytics overview',
        ];
        for ($i = 0; $i < $n; $i++) {
            $title = ($titles[$i % count($titles)]).' (Part '.(intval($i / count($titles)) + 1).')';
            $rows[] = [
                'title' => $title,
                'slug' => Str::slug($title.'-'.$i),
                'category_id' => $catIds[array_rand($catIds)],
                'user_id' => $userIds[array_rand($userIds)],
                'content' => '<p>Detailed walkthrough for '.$title.'. '.str_repeat('Step-by-step instructions follow with screenshots and tips. ', 5).'</p>',
                'excerpt' => 'Learn '.$title.' in this quick guide.',
                'status' => rand(0, 9) < 8 ? 'published' : 'draft',
                'is_featured' => rand(0, 9) < 2,
                'view_count' => rand(0, 5000),
                'helpful_count' => rand(0, 200),
                'not_helpful_count' => rand(0, 30),
                'meta_title' => $title,
                'meta_description' => 'Quick guide on '.$title,
                'created_at' => $now->copy()->subDays(rand(1, 365)),
                'updated_at' => $now,
            ];
            if (count($rows) >= 100) {
                KnowledgeArticle::insertOrIgnore($rows);
                $rows = [];
            }
        }
        if ($rows) {
            KnowledgeArticle::insertOrIgnore($rows);
        }
    }

    private function seedPosts(int $n, array $userIds): void
    {
        $now = now();
        $rows = [];
        $topics = ['Customer support trends', 'AI in helpdesk', 'SLA management tips', 'Reducing ticket volume', 'Team productivity', 'Customer satisfaction metrics', 'Knowledge base strategy', 'Omnichannel support', 'Automation workflows', 'Helpdesk security'];
        for ($i = 0; $i < $n; $i++) {
            $title = $topics[$i % count($topics)].' #'.($i + 1);
            $rows[] = [
                'title' => $title,
                'slug' => Str::slug($title),
                'user_id' => $userIds[array_rand($userIds)],
                'content' => '<p>'.str_repeat($title.'. Detailed analysis and actionable insights for support teams. ', 10).'</p>',
                'excerpt' => 'Insights on '.$title,
                'featured_image' => null,
                'category' => ['Industry', 'Product', 'Tutorial', 'Case Study'][rand(0, 3)],
                'status' => 'published',
                'published_at' => $now->copy()->subDays(rand(1, 180)),
                'created_at' => $now->copy()->subDays(rand(1, 365)),
                'updated_at' => $now,
            ];
        }
        Post::insertOrIgnore($rows);
    }

    private function seedTickets(int $n, array $customerIds, array $agentIds): array
    {
        $now = now();
        $rows = [];
        $departments = Department::pluck('id')->all();
        $categories = Category::pluck('id')->all();
        $subjects = [
            'Cannot login to my account', 'Payment failed but card was charged', 'How do I export my data?',
            'API rate limit issue', 'Email notifications not received', 'Bug: dashboard shows wrong stats',
            'Feature request: dark mode', 'Integration with X not working', 'Slow performance on reports page',
            'Need help configuring SSO', 'Password reset email not arriving', 'Mobile app crashing on iOS',
            'Webhook deliveries failing', 'Custom field validation error', 'Cannot upload attachments larger than 10MB',
        ];
        $statuses = ['open', 'in_progress', 'answered', 'resolved', 'closed'];
        $priorities = ['low', 'medium', 'high', 'urgent'];
        $sources = ['web', 'email', 'chat', 'api'];

        for ($i = 0; $i < $n; $i++) {
            $createdAt = $now->copy()->subDays(rand(0, 180))->subHours(rand(0, 23));
            $status = $statuses[array_rand($statuses)];
            $closedAt = in_array($status, ['resolved', 'closed']) ? $createdAt->copy()->addHours(rand(1, 72)) : null;
            $rows[] = [
                'uid' => 'TKT-'.strtoupper(Str::random(8)),
                'user_id' => $customerIds[array_rand($customerIds)],
                'assigned_to' => rand(0, 9) < 7 ? $agentIds[array_rand($agentIds)] : null,
                'department_id' => $departments[array_rand($departments)],
                'category_id' => $categories[array_rand($categories)],
                'subject' => $subjects[array_rand($subjects)],
                'body' => 'Detailed description of the issue. '.Str::random(80),
                'priority' => $priorities[array_rand($priorities)],
                'status' => $status,
                'source' => $sources[array_rand($sources)],
                'sla_due_at' => $createdAt->copy()->addHours(24),
                'closed_at' => $closedAt,
                'first_response_at' => $createdAt->copy()->addMinutes(rand(5, 240)),
                'resolved_at' => $closedAt,
                'satisfaction_rating' => $closedAt && rand(0, 9) < 4 ? rand(1, 5) : null,
                'is_starred' => rand(0, 19) === 0,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];
            if (count($rows) >= 500) {
                Ticket::insert($rows);
                $rows = [];
            }
        }
        if ($rows) {
            Ticket::insert($rows);
        }

        return Ticket::where('uid', 'like', 'TKT-%')->pluck('id')->all();
    }

    private function seedTicketReplies(array $ticketIds, array $customerIds, array $agentIds, int $n): void
    {
        $now = now();
        $rows = [];
        $sampleTickets = array_slice($ticketIds, 0, min(count($ticketIds), 1500));
        for ($i = 0; $i < $n; $i++) {
            $isAgent = rand(0, 9) < 6;
            $userId = $isAgent ? $agentIds[array_rand($agentIds)] : $customerIds[array_rand($customerIds)];
            $rows[] = [
                'ticket_id' => $sampleTickets[array_rand($sampleTickets)],
                'user_id' => $userId,
                'body' => 'Reply: '.Str::random(60),
                'is_internal' => $isAgent && rand(0, 9) === 0,
                'source' => 'web',
                'created_at' => $now->copy()->subDays(rand(0, 60)),
                'updated_at' => $now,
            ];
            if (count($rows) >= 500) {
                TicketReply::insert($rows);
                $rows = [];
            }
        }
        if ($rows) {
            TicketReply::insert($rows);
        }
    }

    private function seedConversations(int $convN, int $msgN, array $customerIds, array $agentIds): void
    {
        $now = now();
        $convRows = [];
        for ($i = 0; $i < $convN; $i++) {
            $convRows[] = [
                'user_id' => $customerIds[array_rand($customerIds)],
                'assigned_to' => $agentIds[array_rand($agentIds)],
                'status' => ['open', 'closed'][rand(0, 1)],
                'last_message_at' => $now->copy()->subMinutes(rand(0, 60 * 24 * 30)),
                'created_at' => $now->copy()->subDays(rand(0, 60)),
                'updated_at' => $now,
            ];
        }
        Conversation::insert($convRows);
        $convIds = Conversation::orderBy('id', 'desc')->limit($convN)->pluck('id')->all();

        $msgRows = [];
        for ($i = 0; $i < $msgN; $i++) {
            $isCustomer = rand(0, 9) < 5;
            $userId = $isCustomer ? $customerIds[array_rand($customerIds)] : $agentIds[array_rand($agentIds)];
            $msgRows[] = [
                'conversation_id' => $convIds[array_rand($convIds)],
                'user_id' => $userId,
                'body' => 'Message: '.Str::random(50),
                'type' => 'text',
                'metadata' => null,
                'read_at' => rand(0, 9) < 7 ? $now : null,
                'created_at' => $now->copy()->subMinutes(rand(0, 60 * 24 * 30)),
                'updated_at' => $now,
            ];
            if (count($msgRows) >= 500) {
                ConversationMessage::insert($msgRows);
                $msgRows = [];
            }
        }
        if ($msgRows) {
            ConversationMessage::insert($msgRows);
        }
    }

    private function summary(): void
    {
        $this->command->info('==== Summary ====');
        $this->command->info('Users: '.User::count().' (customers: '.User::whereHas('roles', fn ($q) => $q->where('name', 'customer'))->count().', agents: '.User::whereHas('roles', fn ($q) => $q->where('name', 'agent'))->count().')');
        $this->command->info('Tickets: '.Ticket::count());
        $this->command->info('Ticket replies: '.TicketReply::count());
        $this->command->info('Conversations: '.Conversation::count().' (messages: '.ConversationMessage::count().')');
        $this->command->info('Knowledge articles: '.KnowledgeArticle::count());
        $this->command->info('Posts: '.Post::count());
        $this->command->info('Services: '.Service::count());
    }
}
