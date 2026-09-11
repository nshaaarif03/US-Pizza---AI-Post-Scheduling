<?php

namespace Database\Seeders;

use App\Models\Campaign;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (Campaign::where('is_demo', true)->exists()) {
            return;
        }
        $campaign = Campaign::create([
            'campaign_goal' => 'Bring people together over pizza', 'target_audience' => 'University students and young adults',
            'product_promotion' => 'Pizza night — fictional content, no official promotion', 'platforms' => ['Instagram', 'Facebook', 'TikTok'],
            'tone' => 'Fun', 'start_date' => today(), 'end_date' => today()->addDays(13), 'is_demo' => true,
            'additional_instructions' => 'Synthetic demonstration content only. No company data or official offers.',
        ]);
        $ideas = [
            ['Instagram', 'Carousel', 'Good pizza. Better company.', 'The best seat at the table? The one next to your pizza crew. Who are you sharing with?', 'Tag your pizza crew'],
            ['TikTok', 'Short video', 'The first-slice feeling', 'That first-slice feeling never gets old. Show us your pizza-night moment.', 'Share your pizza moment'],
            ['Facebook', 'Community', 'Your weekend, served together', 'Some plans are worth making time for. Gather your favourite people and make your next catch-up a pizza night.', 'Plan a catch-up'],
            ['Instagram', 'Photo', 'A little pause. A slice of happiness.', 'Close the laptop. Call your friends. Make a little time for a slice together.', 'Tag someone who needs a break'],
            ['Facebook', 'Engagement', 'Settle the great topping debate', 'Every pizza crew has a topping debate. What is your must-have topping, and who disagrees?', 'Tell us your favourite'],
            ['TikTok', 'Short video', 'POV: the group chat made a plan', 'When the group chat finally agrees on pizza night.', 'Send this to your group chat'],
            ['Instagram', 'Carousel', 'The anatomy of a pizza night', 'One table. A few friends. Plenty to catch up on.', 'Save this for your next catch-up'],
            ['Facebook', 'Community', 'Make room at the table', 'A simple invitation can turn an ordinary evening into a good memory. Who is coming to your next pizza night?', 'Invite a friend'],
        ];
        foreach ($ideas as $i => [$platform, $type, $idea, $caption, $cta]) {
            $status = $i < 3 ? 'Scheduled' : ($i < 5 ? 'Approved' : 'Draft');
            $date = today()->addDays($i + 1);
            $campaign->posts()->create([
                'platform' => $platform, 'content_type' => $type, 'post_idea' => $idea, 'caption' => $caption, 'call_to_action' => $cta,
                'hook' => $platform === 'TikTok' ? 'Pizza night, from the first frame.' : null,
                'hashtags' => ['#PizzaNight', '#BetterTogether'], 'ai_reason' => 'Fictional sample: early evening may help people plan a shared meal. This timing has not been validated against audience analytics.',
                'planned_date' => $date, 'suggested_time' => '17:00', 'status' => $status,
                'scheduled_date' => $status === 'Scheduled' ? $date : null, 'scheduled_time' => $status === 'Scheduled' ? '17:00' : null,
            ]);
        }
    }
}
