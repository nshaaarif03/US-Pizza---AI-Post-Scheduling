<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    public const PLATFORMS = ['Instagram', 'Facebook', 'TikTok'];

    public const STATUSES = ['Draft', 'Approved', 'Scheduled', 'Posted'];

    protected $fillable = ['platform', 'content_type', 'post_idea', 'hook', 'caption', 'call_to_action', 'hashtags', 'ai_reason', 'planned_date', 'suggested_time', 'scheduled_date', 'scheduled_time', 'status', 'version'];

    protected function casts(): array
    {
        return ['planned_date' => 'date', 'scheduled_date' => 'date', 'hashtags' => 'array', 'version' => 'integer'];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function scheduledAt(): ?CarbonImmutable
    {
        return $this->scheduled_date ? CarbonImmutable::parse($this->scheduled_date->format('Y-m-d').' '.$this->scheduled_time, config('app.timezone')) : null;
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'Scheduled')->where(function ($q) {
            $q->where('scheduled_date', '>', today()->toDateString())
                ->orWhere(fn ($q) => $q->where('scheduled_date', today()->toDateString())->where('scheduled_time', '>=', now()->format('H:i:s')));
        })->orderBy('scheduled_date')->orderBy('scheduled_time');
    }
}
