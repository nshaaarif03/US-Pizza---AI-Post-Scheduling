<?php

namespace App\Services\SocialMedia;

use App\Models\Post;

class InstagramPublisher implements SocialMediaPublisherInterface
{
    public function publish(Post $post): array
    {
        return ['connected' => false, 'message' => 'Instagram publishing integration is not connected yet.', 'url' => 'https://www.instagram.com/'];
    }
}
