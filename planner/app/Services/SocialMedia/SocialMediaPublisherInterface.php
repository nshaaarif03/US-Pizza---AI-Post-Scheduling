<?php

namespace App\Services\SocialMedia;

use App\Models\Post;

interface SocialMediaPublisherInterface
{
    /** Future implementations must use an authorized API and verify its response. */
    public function publish(Post $post): array;
}
