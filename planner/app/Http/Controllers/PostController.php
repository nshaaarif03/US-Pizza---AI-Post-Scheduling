<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostRequest;
use App\Models\Post;
use App\Services\PostWorkflow;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate(['status' => ['nullable', Rule::in(Post::STATUSES)], 'platform' => ['nullable', Rule::in(Post::PLATFORMS)], 'campaign' => ['nullable', 'integer', 'exists:campaigns,id']]);
        $posts = Post::with('campaign')->when($data['status'] ?? null, fn ($q, $v) => $q->where('status', $v))->when($data['platform'] ?? null, fn ($q, $v) => $q->where('platform', $v))->when($data['campaign'] ?? null, fn ($q, $v) => $q->where('campaign_id', $v))->latest('id')->paginate(12)->withQueryString();

        return view('posts.index', compact('posts'));
    }

    public function show(Post $post)
    {
        return view('posts.show', ['post' => $post->load('campaign')]);
    }

    public function edit(Post $post)
    {
        return to_route('posts.show', $post);
    }

    public function update(PostRequest $request, Post $post, PostWorkflow $workflow)
    {
        $workflow->change($post, $request->integer('version'), ['Draft', 'Approved'], function ($current) use ($request) {
            $current->fill($request->safe()->except('version'));
            $current->status = 'Draft';
            $current->save();
        });

        return to_route('posts.show', $post)->with('success', 'Changes saved. Review and approve this version before scheduling.');
    }

    public function approve(Request $request, Post $post, PostWorkflow $workflow)
    {
        $request->validate(['version' => ['required', 'integer', 'min:1']]);
        $workflow->change($post, $request->integer('version'), ['Draft'], fn ($p) => $p->update(['status' => 'Approved']));

        return to_route('posts.show', $post)->with('success', 'Post approved. You can now schedule it.');
    }

    public function markPosted(Request $request, Post $post, PostWorkflow $workflow)
    {
        $request->validate(['version' => ['required', 'integer', 'min:1']]);
        $workflow->change($post, $request->integer('version'), ['Scheduled'], fn ($p) => $p->update(['status' => 'Posted']));

        return to_route('posts.show', $post)->with('success', 'Post marked as published. Great work!');
    }

    public function destroy(Request $request, Post $post, PostWorkflow $workflow)
    {
        $request->validate(['version' => ['required', 'integer', 'min:1']]);
        $workflow->change($post, $request->integer('version'), ['Draft'], fn ($p) => $p->delete());

        return to_route('posts.index')->with('success', 'Draft deleted.');
    }
}
