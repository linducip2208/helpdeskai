<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $posts = Post::with('user')
            ->where('status', 'published')
            ->when($request->category, fn($q) => $q->where('category', $request->category))
            ->latest()
            ->paginate(12);

        $categories = Post::where('status', 'published')
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('blog.index', [
            'posts' => $posts,
            'categories' => $categories,
            'activeCategory' => $request->category,
        ]);
    }

    public function category(string $category): View
    {
        $posts = Post::with('user')
            ->where('status', 'published')
            ->where('category', $category)
            ->latest()
            ->paginate(12);

        $categories = Post::where('status', 'published')
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('blog.index', [
            'posts' => $posts,
            'categories' => $categories,
            'activeCategory' => $category,
        ]);
    }

    public function show(string $slug): View
    {
        $post = Post::where('slug', $slug)->where('status', 'published')->firstOrFail();
        $post->load('user');

        return view('blog.show', ['post' => $post]);
    }

    public function feed(): Response
    {
        $posts = Post::with('user')
            ->where('status', 'published')
            ->latest('published_at')
            ->limit(50)
            ->get();

        $siteName = config('app.name', 'HelpDesk AI');
        $now = now()->toRssString();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">' . "\n";
        $xml .= "  <channel>\n";
        $xml .= '    <title>' . htmlspecialchars($siteName . ' Blog') . "</title>\n";
        $xml .= '    <link>' . url('/blog') . "</link>\n";
        $xml .= '    <description>' . htmlspecialchars('Latest articles from ' . $siteName) . "</description>\n";
        $xml .= "    <language>en</language>\n";
        $xml .= '    <lastBuildDate>' . $now . "</lastBuildDate>\n";
        $xml .= '    <atom:link href="' . url('/blog/feed.xml') . '" rel="self" type="application/rss+xml" />' . "\n";

        foreach ($posts as $post) {
            $link = url('/blog/' . $post->slug);
            $pubDate = ($post->published_at ?? $post->created_at)->toRssString();
            $desc = $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->content), 300);

            $xml .= "    <item>\n";
            $xml .= '      <title>' . htmlspecialchars($post->title) . "</title>\n";
            $xml .= '      <link>' . $link . "</link>\n";
            $xml .= '      <guid isPermaLink="true">' . $link . "</guid>\n";
            $xml .= '      <pubDate>' . $pubDate . "</pubDate>\n";
            if ($post->author?->name ?? $post->user?->name) {
                $xml .= '      <author>' . htmlspecialchars(($post->user?->name ?? 'Author')) . "</author>\n";
            }
            if ($post->category) {
                $xml .= '      <category>' . htmlspecialchars($post->category) . "</category>\n";
            }
            $xml .= '      <description>' . htmlspecialchars($desc) . "</description>\n";
            $xml .= "    </item>\n";
        }

        $xml .= "  </channel>\n</rss>";

        return response($xml, 200, ['Content-Type' => 'application/rss+xml; charset=UTF-8']);
    }
}
