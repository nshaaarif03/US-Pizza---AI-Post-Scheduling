<?php

namespace App\Http\Requests;

use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'campaign_goal' => ['required', 'string', 'max:255'],
            'target_audience' => ['required', 'string', 'max:255'],
            'product_promotion' => ['required', 'string', 'max:255'],
            'platforms' => ['required', 'array', 'min:1', 'max:3'],
            'platforms.*' => ['required', Rule::in(Post::PLATFORMS), 'distinct'],
            'tone' => ['required', Rule::in(['Casual', 'Fun', 'Professional', 'Promotional'])],
            'start_date' => ['bail', 'required', 'string', 'date_format:Y-m-d', 'after_or_equal:today', 'before_or_equal:'.today()->addYear()->toDateString()],
            'end_date' => ['bail', 'required', 'string', 'date_format:Y-m-d'],
            'additional_instructions' => ['nullable', 'string', 'max:2000'],
            'mode' => ['required', Rule::in(['gemini', 'sample'])],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->has('start_date') || $validator->errors()->has('end_date')) {
                return;
            }
            if ($this->input('end_date') < $this->input('start_date')) {
                $validator->errors()->add('end_date', 'The end date must be on or after the start date.');

                return;
            }
            if (Carbon::parse($this->input('start_date'))->diffInDays(Carbon::parse($this->input('end_date'))) > 13) {
                $validator->errors()->add('end_date', 'Campaigns can cover up to 14 days.');
            }
        });
    }
}
