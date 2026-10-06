<?php

namespace App\Console\Commands;

use App\Models\KnowledgeArticle;
use App\Models\Post;
use App\Models\Service;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\File;

class ExportPseo extends Command
{
    protected $signature = 'pseo:export {--path=public/pseo} {--base-url=}';

    protected $description = 'Render all pSEO routes (best-helpdesk, compare, alternatives) + sitemap.xml to static .html files for Search Console submission';

    public function handle(Router $router): int
    {
        $outPath = base_path($this->option('path'));
        $baseUrl = $this->option('base-url') ?: (string) config('app.url');

        if (! File::isDirectory($outPath)) {
            File::makeDirectory($outPath, 0755, true);
        }

        $this->info("Exporting pSEO pages to: {$outPath}");

        $urls = $this->collectUrls();
        $rendered = 0;
        $failed = 0;

        foreach ($urls as $relUrl) {
            $filePath = $this->urlToFilePath($outPath, $relUrl);
            File::ensureDirectoryExists(dirname($filePath));

            try {
                $html = $this->renderRoute($router, $relUrl);
                File::put($filePath, $html);
                $rendered++;
                $this->line("  ✓ {$relUrl} → ".str_replace($outPath, '', $filePath));
            } catch (\Throwable $e) {
                $failed++;
                $this->error("  ✗ {$relUrl} — {$e->getMessage()}");
            }
        }

        $sitemapPath = $outPath.DIRECTORY_SEPARATOR.'sitemap.xml';
        File::put($sitemapPath, $this->buildSitemap($urls, $baseUrl));
        $this->info("\nsitemap.xml → {$sitemapPath} (".count($urls).' URLs)');

        $indexPath = $outPath.DIRECTORY_SEPARATOR.'index.html';
        File::put($indexPath, $this->buildIndex($urls, $baseUrl));
        $this->info("index.html → {$indexPath}");

        $this->newLine();
        $this->info("Done. Rendered: {$rendered}. Failed: {$failed}.");
        $this->comment("Submit `{$baseUrl}/pseo/sitemap.xml` to Google Search Console.");
        $this->comment('Or serve directly from any static host (Cloudflare Pages, Netlify, S3, etc.).');

        return self::SUCCESS;
    }

    private function collectUrls(): array
    {
        $urls = [
            '/best-helpdesk-software',
            '/best-helpdesk-software/'.date('Y'),
            '/best-helpdesk-software/'.(date('Y') + 1),
        ];

        $services = Service::where('is_active', true)->get();

        foreach ($services as $svc) {
            $urls[] = '/alternatives-to/'.$svc->slug;
        }

        $serviceList = $services->take(20)->values();
        foreach ($serviceList as $a) {
            foreach ($serviceList as $b) {
                if ($a->id < $b->id) {
                    $urls[] = '/compare/'.$a->slug.'-vs-'.$b->slug;
                }
            }
        }

        return array_values(array_unique($urls));
    }

    private function renderRoute(Router $router, string $url): string
    {
        $request = Request::create($url, 'GET');
        $request->headers->set('User-Agent', 'pseo-export/1.0');

        app()->instance('request', $request);

        $response = $router->dispatch($request);

        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException('HTTP '.$response->getStatusCode());
        }

        return $response->getContent();
    }

    private function urlToFilePath(string $outPath, string $url): string
    {
        $clean = trim($url, '/');
        if ($clean === '') {
            return $outPath.DIRECTORY_SEPARATOR.'index.html';
        }
        $clean = preg_replace('/[^a-zA-Z0-9\/_-]/', '_', $clean);

        return $outPath.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $clean).'.html';
    }

    private function buildSitemap(array $urls, string $baseUrl): string
    {
        $extra = [
            '/' => '1.0',
            '/blog' => '0.8',
            '/services' => '0.8',
            '/knowledge-base' => '0.8',
            '/contact' => '0.5',
            '/docs' => '0.6',
        ];

        $all = [];
        foreach ($extra as $u => $p) {
            $all[] = ['loc' => rtrim($baseUrl, '/').$u, 'priority' => $p];
        }
        foreach ($urls as $u) {
            $all[] = ['loc' => rtrim($baseUrl, '/').$u, 'priority' => '0.7'];
        }

        if (class_exists(Post::class)) {
            foreach (Post::where('status', 'published')->get(['slug', 'updated_at']) as $p) {
                $all[] = ['loc' => rtrim($baseUrl, '/').'/blog/'.$p->slug, 'priority' => '0.6', 'lastmod' => $p->updated_at?->toAtomString()];
            }
        }

        if (class_exists(KnowledgeArticle::class)) {
            foreach (KnowledgeArticle::where('status', 'published')->get(['slug', 'updated_at']) as $a) {
                $all[] = ['loc' => rtrim($baseUrl, '/').'/knowledge-base/'.$a->slug, 'priority' => '0.6', 'lastmod' => $a->updated_at?->toAtomString()];
            }
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($all as $u) {
            $xml .= "  <url>\n    <loc>".htmlspecialchars($u['loc'])."</loc>\n";
            if (! empty($u['lastmod'])) {
                $xml .= "    <lastmod>{$u['lastmod']}</lastmod>\n";
            }
            $xml .= "    <changefreq>weekly</changefreq>\n    <priority>{$u['priority']}</priority>\n  </url>\n";
        }
        $xml .= '</urlset>';

        return $xml;
    }

    private function buildIndex(array $urls, string $baseUrl): string
    {
        $rows = '';
        foreach ($urls as $u) {
            $file = ltrim($u, '/').'.html';
            $rows .= "<li><a href=\"{$file}\">{$u}</a></li>\n";
        }
        $count = count($urls);
        $base = htmlspecialchars($baseUrl);

        return <<<HTML
<!doctype html><html><head>
<meta charset="utf-8"><title>pSEO Export Index ({$count} pages)</title>
<style>body{font:14px ui-sans-serif,system-ui;max-width:900px;margin:2rem auto;padding:1rem}
li{padding:.25rem 0}a{color:#4f46e5;text-decoration:none}a:hover{text-decoration:underline}
.meta{background:#f1f5f9;padding:1rem;border-radius:.5rem;margin:1rem 0}</style>
</head><body>
<h1>pSEO Export</h1>
<div class="meta">
  <p>Base URL: <code>{$base}</code></p>
  <p>Total static pages: <strong>{$count}</strong></p>
  <p>Sitemap: <a href="sitemap.xml">sitemap.xml</a></p>
  <p>Submit to Google Search Console: <code>{$base}/pseo/sitemap.xml</code></p>
</div>
<h2>All Pages</h2>
<ul>{$rows}</ul>
</body></html>
HTML;
    }
}
