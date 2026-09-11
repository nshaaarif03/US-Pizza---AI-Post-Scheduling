<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\SocialMedia\FacebookPublisher;
use App\Services\SocialMedia\InstagramPublisher;
use App\Services\SocialMedia\TikTokPublisher;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SocialMediaController extends Controller
{
    public function store(Request $request, Post $post)
    {
        abort_unless(in_array($post->status, ['Approved', 'Scheduled'], true), 403, 'Approve this post before continuing.');
        $data = $request->validate(['platform' => ['required', Rule::in(Post::PLATFORMS)]]);
        $publisher = match ($data['platform']) {
            'Instagram' => app(InstagramPublisher::class), 'Facebook' => app(FacebookPublisher::class), 'TikTok' => app(TikTokPublisher::class)
        };

        return to_route('posts.show', $post)->with('handoff', $publisher->publish($post) + ['platform' => $data['platform']]);
    }
}
