<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\Seo\IndexNowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        return view('admin.posts.index', [
            'posts' => Post::with('user')->latest()->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.posts.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'status' => 'required|in:draft,published',
        ]);

        $data['user_id'] = $request->user()->id;
        $data['slug'] = Str::slug($data['title']);
        $data['published_at'] = $data['status'] === 'published' ? now() : null;

        $post = Post::create($data);

        if ($post->status === 'published') {
            app(IndexNowService::class)->submit(url('/blog/'.$post->slug), true);
        }

        return redirect()->route('admin.posts.index')->with('success', 'Post created.');
    }

    public function edit(Post $post): View
    {
        return view('admin.posts.edit', ['post' => $post]);
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'status' => 'required|in:draft,published',
        ]);

        if ($post->status !== 'published' && $data['status'] === 'published') {
            $data['published_at'] = now();
        }

        $post->update($data);

        if ($post->status === 'published') {
            app(IndexNowService::class)->submit(url('/blog/'.$post->slug), true);
        }

        return redirect()->back()->with('success', 'Post updated.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        return redirect()->route('admin.posts.index')->with('success', 'Post deleted.');
    }
}
