<?php

namespace App\Services\SocialMedia;

use App\Models\Post;

class FacebookPublisher implements SocialMediaPublisherInterface
{
    public function publish(Post $post): array
    {
        return ['connected' => false, 'message' => 'Facebook publishing integration is not connected yet.', 'url' => 'https://www.facebook.com/'];
    }
}
