<?php

namespace App\Http\Requests;

use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('hashtags', ''))) {
            $this->merge(['hashtags' => preg_split('/\s+/', trim($this->input('hashtags', '')), -1, PREG_SPLIT_NO_EMPTY)]);
        }
    }

    public function rules(): array
    {
        return [
            'version' => ['required', 'integer', 'min:1'],
            'platform' => ['required', Rule::in(Post::PLATFORMS)],
            'post_idea' => ['required', 'string', 'max:255'],
            'hook' => ['nullable', 'string', 'max:500'],
            'caption' => ['required', 'string', 'max:2200'],
            'call_to_action' => ['required', 'string', 'max:255'],
            'hashtags' => ['array', 'max:5'],
            'hashtags.*' => ['string', 'max:60', 'regex:/^#[\pL\pN_]+$/u', 'distinct'],
            'planned_date' => ['bail', 'required', 'string', 'date_format:Y-m-d'],
            'suggested_time' => ['bail', 'required', 'string', 'date_format:H:i'],
        ];
    }
}
