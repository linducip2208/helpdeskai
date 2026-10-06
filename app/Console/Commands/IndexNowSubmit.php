<?php

namespace App\Console\Commands;

use App\Models\KnowledgeArticle;
use App\Models\Post;
use App\Models\Service;
use App\Services\Seo\IndexNowService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class IndexNowSubmit extends Command
{
    protected $signature = 'seo:indexnow {url? : A single URL to submit} {--force : Resubmit even if cached}';

    protected $description = 'Submit public URLs to IndexNow (Bing, Yandex, Seznam, Naver)';

    public function handle(IndexNowService $indexNow): int
    {
        if (! $indexNow->key()) {
            $this->error('No INDEXNOW_KEY configured. Add it to .env.');

            return self::FAILURE;
        }

        if ($single = $this->argument('url')) {
            $count = $indexNow->submit($single, (bool) $this->option('force'));
            $this->info("Submitted {$count} URL to IndexNow.");

            return self::SUCCESS;
        }

        $urls = [
            url('/'),
            url('/blog'),
            url('/services'),
            url('/knowledge-base'),
            url('/docs'),
            url('/best-helpdesk-software'),
            url('/best-helpdesk-software/'.date('Y')),
        ];

        if (Schema::hasTable('posts')) {
            foreach (Post::where('status', 'published')->pluck('slug') as $slug) {
                $urls[] = url('/blog/'.$slug);
            }
        }

        if (Schema::hasTable('knowledge_articles')) {
            foreach (KnowledgeArticle::where('status', 'published')->pluck('slug') as $slug) {
                $urls[] = url('/knowledge-base/'.$slug);
            }
        }

        if (Schema::hasTable('services')) {
            foreach (Service::where('is_active', true)->pluck('slug') as $slug) {
                $urls[] = url('/services/'.$slug);
                $urls[] = url('/alternatives-to/'.$slug);
            }
        }

        $count = $indexNow->submit($urls, (bool) $this->option('force'));
        $this->info("Submitted {$count} new URL(s) to IndexNow.");

        return self::SUCCESS;
    }
}
