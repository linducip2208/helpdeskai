<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeArticle;
use App\Models\Post;
use App\Models\Service;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProgrammaticSeoController extends Controller
{
    public function sitemap(): Response
    {
        $urls = [
            ['loc' => url('/'), 'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => url('/blog'), 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['loc' => url('/services'), 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['loc' => url('/knowledge-base'), 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['loc' => url('/contact'), 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['loc' => url('/docs'), 'priority' => '0.6', 'changefreq' => 'monthly'],
            ['loc' => url('/best-helpdesk-software'), 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['loc' => url('/best-helpdesk-software/' . date('Y')), 'priority' => '0.9', 'changefreq' => 'weekly'],
        ];

        if (Schema::hasTable('services')) {
            foreach (Service::where('is_active', true)->get() as $svc) {
                $urls[] = ['loc' => url('/services/' . $svc->slug), 'priority' => '0.7', 'changefreq' => 'monthly'];
                $urls[] = ['loc' => url('/alternatives-to/' . $svc->slug), 'priority' => '0.7', 'changefreq' => 'monthly'];
            }
            $services = Service::where('is_active', true)->limit(20)->get();
            foreach ($services as $a) {
                foreach ($services as $b) {
                    if ($a->id < $b->id) {
                        $urls[] = ['loc' => url('/compare/' . $a->slug . '-vs-' . $b->slug), 'priority' => '0.6', 'changefreq' => 'monthly'];
                    }
                }
            }
        }

        if (Schema::hasTable('posts')) {
            foreach (Post::where('status', 'published')->get(['slug', 'updated_at']) as $p) {
                $urls[] = ['loc' => url('/blog/' . $p->slug), 'priority' => '0.6', 'changefreq' => 'monthly', 'lastmod' => $p->updated_at?->toAtomString()];
            }
            foreach (Post::where('status', 'published')->whereNotNull('category')->distinct()->pluck('category') as $cat) {
                $urls[] = ['loc' => url('/blog/category/' . $cat), 'priority' => '0.5', 'changefreq' => 'weekly'];
            }
        }

        if (Schema::hasTable('knowledge_articles')) {
            foreach (KnowledgeArticle::where('status', 'published')->get(['slug', 'updated_at']) as $a) {
                $urls[] = ['loc' => url('/knowledge-base/' . $a->slug), 'priority' => '0.6', 'changefreq' => 'monthly', 'lastmod' => $a->updated_at?->toAtomString()];
            }
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $u) {
            $xml .= "  <url>\n    <loc>" . htmlspecialchars($u['loc']) . "</loc>\n";
            if (!empty($u['lastmod'])) $xml .= "    <lastmod>" . $u['lastmod'] . "</lastmod>\n";
            $xml .= "    <changefreq>" . $u['changefreq'] . "</changefreq>\n    <priority>" . $u['priority'] . "</priority>\n  </url>\n";
        }
        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    public function robots(): Response
    {
        $content = "User-agent: *\nAllow: /\n\n";
        $content .= "Sitemap: " . url('/sitemap.xml') . "\n";

        return response($content, 200, ['Content-Type' => 'text/plain']);
    }


    public function bestHelpdesk(int $year = null): View
    {
        $year = $year ?? (int) date('Y');

        $items = Service::where('is_active', true)
            ->orderBy('sort_order')
            ->take(10)
            ->get();

        $title = "Best Helpdesk Software in {$year}";
        $description = "Discover the top helpdesk solutions in {$year}. Compare features, pricing, and reviews to find the perfect customer support platform for your business.";

        $faqs = [
            ['q' => "What is the best helpdesk software in {$year}?", 'a' => "The best helpdesk software depends on your team size, budget, and feature requirements. Our top picks are ranked based on features, ease of use, AI capabilities, and customer reviews."],
            ['q' => 'How much does helpdesk software cost?', 'a' => 'Pricing varies from free open-source solutions to enterprise platforms. Most mid-range helpdesk tools cost between $15-$99 per agent per month. Some one-time purchase options are also available.'],
            ['q' => 'What features should I look for in helpdesk software?', 'a' => 'Key features include: ticket management, live chat, knowledge base, AI automation, SLA tracking, multi-channel support (email, chat, social), reporting & analytics, and API integrations.'],
            ['q' => 'Is AI-powered helpdesk worth it?', 'a' => 'Yes. AI features like smart ticket classification, automated response suggestions, and sentiment analysis can reduce response times by 50-70% and improve customer satisfaction significantly.'],
            ['q' => 'Can I try helpdesk software before buying?', 'a' => 'Most helpdesk platforms offer free trials ranging from 14-30 days. Some also provide free tiers with limited features for small teams.'],
        ];

        return view('seo.best-helpdesk', [
            'year' => $year,
            'items' => $items,
            'title' => $title,
            'description' => $description,
            'faqs' => $faqs,
            'seoTitle' => "{$title} — Top 10 Helpdesk Solutions Reviewed",
            'seoDescription' => $description,
        ]);
    }

    public function compare(string $slugs): View
    {
        $parts = explode('-vs-', $slugs);
        if (count($parts) !== 2) abort(404);

        $a = Service::where('slug', $parts[0])->where('is_active', true)->first();
        $b = Service::where('slug', $parts[1])->where('is_active', true)->first();

        if (!$a || !$b) abort(404);

        $title = "{$a->title} vs {$b->title}: Detailed Comparison";
        $description = "Compare {$a->title} and {$b->title} side-by-side. Features, pricing, pros & cons, and which one is right for your business.";

        return view('seo.compare', [
            'a' => $a,
            'b' => $b,
            'seoTitle' => $title,
            'seoDescription' => $description,
        ]);
    }

    public function alternatives(string $slug): View
    {
        $service = Service::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $alternatives = Service::where('is_active', true)
            ->where('id', '!=', $service->id)
            ->orderBy('sort_order')
            ->limit(8)
            ->get();

        $title = "Top Alternatives to {$service->title}";
        $description = "Looking for an alternative to {$service->title}? Compare {$alternatives->count()} similar helpdesk solutions with features, pricing, and reviews.";

        return view('seo.alternatives', [
            'service' => $service,
            'alternatives' => $alternatives,
            'seoTitle' => $title,
            'seoDescription' => $description,
        ]);
    }
}
