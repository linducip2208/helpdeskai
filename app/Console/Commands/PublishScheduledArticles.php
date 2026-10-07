<?php

namespace App\Console\Commands;

use App\Models\KnowledgeArticle;
use Illuminate\Console\Command;

class PublishScheduledArticles extends Command
{
    protected $signature = 'kb:publish';

    protected $description = 'Publish draft knowledge articles whose published_at has passed.';

    public function handle(): int
    {
        $count = KnowledgeArticle::where('status', 'draft')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->update(['status' => 'published']);

        $this->info("Published {$count} scheduled article(s).");

        return self::SUCCESS;
    }
}
