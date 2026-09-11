<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    protected $fillable = ['campaign_goal', 'target_audience', 'product_promotion', 'platforms', 'tone', 'start_date', 'end_date', 'additional_instructions', 'is_demo'];

    protected function casts(): array
    {
        return ['platforms' => 'array', 'start_date' => 'date', 'end_date' => 'date', 'is_demo' => 'boolean'];
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
