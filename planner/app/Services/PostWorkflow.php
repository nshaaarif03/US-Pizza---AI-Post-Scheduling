<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PostWorkflow
{
    public function change(Post $post, int $version, array $allowed, callable $action): void
    {
        DB::transaction(function () use ($post, $version, $allowed, $action) {
            $current = Post::query()->lockForUpdate()->findOrFail($post->id);
            if ($current->version !== $version) {
                throw ValidationException::withMessages(['post' => 'This post has changed. Reload the page before continuing.']);
            }
            if (! in_array($current->status, $allowed, true)) {
                throw ValidationException::withMessages(['post' => 'This action is unavailable for the current post status.']);
            }
            $current->version++;
            $action($current);
        });
    }
}
