<?php
namespace App\Http\Controllers;

use App\Models\NewsPost;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index()
    {
        $posts = NewsPost::with('author')->orderBy('created_at', 'desc')->paginate(20);
        return view('news.index', compact('posts'));
    }

    public function create()
    {
        return view('news.create', ['post' => null]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'    => 'required|string|max:255',
            'body'     => 'required|string',
            'category' => 'required|in:ZIMRA Update,System,General',
        ]);

        $published = $request->boolean('is_published');

        NewsPost::create([
            'title'        => $request->title,
            'body'         => $request->body,
            'category'     => $request->category,
            'is_published' => $published,
            'published_at' => $published ? now() : null,
            'user_id'      => auth()->id(),
        ]);

        return redirect()->route('news.index')->with('success', 'Post created successfully.');
    }

    public function edit($id)
    {
        $post = NewsPost::findOrFail($id);
        return view('news.create', compact('post'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title'    => 'required|string|max:255',
            'body'     => 'required|string',
            'category' => 'required|in:ZIMRA Update,System,General',
        ]);

        $post      = NewsPost::findOrFail($id);
        $published = $request->boolean('is_published');

        $post->update([
            'title'        => $request->title,
            'body'         => $request->body,
            'category'     => $request->category,
            'is_published' => $published,
            'published_at' => $published && !$post->published_at ? now() : $post->published_at,
        ]);

        return redirect()->route('news.index')->with('success', 'Post updated successfully.');
    }

    public function destroy($id)
    {
        NewsPost::findOrFail($id)->delete();
        return redirect()->route('news.index')->with('success', 'Post deleted.');
    }

    public function togglePublish($id)
    {
        $post              = NewsPost::findOrFail($id);
        $post->is_published = !$post->is_published;

        if ($post->is_published && !$post->published_at) {
            $post->published_at = now();
        }

        $post->save();

        return response()->json([
            'success'      => true,
            'is_published' => $post->is_published,
        ]);
    }
}
