<?php

namespace App\Services\SocialMedia;

use App\Models\Post;

class TikTokPublisher implements SocialMediaPublisherInterface
{
    public function publish(Post $post): array
    {
        return ['connected' => false, 'message' => 'TikTok publishing integration is not connected yet.', 'url' => 'https://www.tiktok.com/'];
    }
}
