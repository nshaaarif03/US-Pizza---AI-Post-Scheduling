<?php

namespace App\Services;

use Carbon\CarbonPeriod;

class SamplePlanService
{
    private array $instagram = [
        ['The first-bite feeling', 'That first bite of hot, fresh pizza hits different. Come experience it yourself. 🍕', 'Order yours now', ['#USPizza', '#FirstBite', '#PizzaLovers'], ''],
        ['Good pizza. Better company.', 'The best seat at the table is the one next to your pizza crew. Tag someone you would share a slice with.', 'Tag your pizza crew', ['#PizzaNight', '#BetterTogether'], ''],
        ['A little pause. A slice of happiness.', 'Close the laptop. Call your friends. Make a little time for a slice together — you have earned it.', 'Tag someone who needs a break', ['#TreatYourself', '#PizzaTime', '#USPizza'], ''],
        ['The anatomy of a perfect pizza night', 'One table. A few friends. Good pizza. Plenty to catch up on. What more do you need?', 'Save this for your next catch-up', ['#PizzaNight', '#WeekendVibes', '#USPizza'], ''],
        ['Friday feeling, sorted', 'Let someone else handle dinner tonight. We have got you covered — and so do our toppings.', 'Order now and enjoy', ['#FridayFeeling', '#USPizza', '#Pizza'], ''],
        ['Stack it. Share it. Repeat.', 'Why settle for one when you can share the whole experience? Find a flavour for everyone at the table.', 'Explore our menu', ['#ShareThePizza', '#USPizza', '#PizzaMenu'], ''],
        ['Your weekend plans, upgraded', 'Swap the indecision for something everyone can agree on. Pizza night — every time.', 'Plan your pizza night', ['#WeekendPlans', '#PizzaNight', '#USPizza'], ''],
    ];

    private array $facebook = [
        ['Your weekend, served together', 'Some plans are worth making time for. Gather your favourite people and make your next catch-up a pizza night. What topping would you order?', 'Plan a catch-up', []],
        ['Settle the great topping debate', 'Every pizza crew has a topping debate. What is your must-have topping — and who disagrees with you most? Tell us in the comments!', 'Tell us your favourite', []],
        ['Make room at the table', 'A simple invitation can turn an ordinary evening into a great memory. Who is coming to your next pizza night? Tag them below.', 'Invite a friend', []],
        ['A mid-week treat that makes sense', 'Not every good evening needs a special occasion. Sometimes all you need is good company and a great pizza. Who are you calling tonight?', 'Order and enjoy', []],
        ['Share a slice. Share the moment.', 'Food tastes better when it is shared. Whether it is a family night in or catching up with friends, pizza makes every moment more memorable.', 'Share this with someone', []],
        ['Your family\'s new Friday tradition', 'Family nights are made for sharing — sharing laughs, stories, and of course pizza. What does your perfect family pizza night look like?', 'Start your tradition', []],
        ['Because good weekends start with better food', 'Whether you are planning a get-together or keeping it casual with the family, a great pizza is always the right call. Tell us what\'s on your weekend menu.', 'Tell us your weekend plan', []],
    ];

    private array $tiktok = [
        ['POV: the group chat made a plan 🍕', 'When the group chat finally decides on pizza night — and everyone actually shows up. Tag your most reliable pizza crew.', 'Send this to your group chat', ['#PizzaNight', '#GroupChat', '#USPizza'], 'Your next pizza night starts here.'],
        ['That first-slice feeling, caught on camera', 'Nobody can hide that reaction to the first slice. Who is the most dramatic in your squad?', 'Share your pizza moment', ['#FirstSlice', '#USPizza', '#PizzaLovers'], 'The first slice hits different every time.'],
        ['POV: You just said "let\'s order pizza"', 'The energy in the room changes instantly. Every. Single. Time. Comment the emoji that describes your squad\'s reaction.', 'Tag your squad', ['#PizzaVibes', '#USPizza', '#TikTokFood'], 'Say "pizza" and see what happens.'],
        ['Choosing a pizza topping, but make it dramatic', 'Classic? Loaded? Half-and-half? The topping debate that never ends. Which side are you on?', 'Comment your pick', ['#ToppingDebate', '#USPizza', '#PizzaTok'], 'It all starts with one question: what topping?'],
        ['The pizza unbox that hits different at 6pm', 'There is a very specific happiness that comes with opening a fresh pizza box after a long day. Who else knows this feeling?', 'Show us your unboxing', ['#PizzaBox', '#USPizza', '#FoodTok'], 'Opening a pizza box should be an Olympic sport.'],
        ['When you ordered pizza and it actually looks like the picture', 'The glow-up is real. Rate this out of 10 in the comments.', 'Order and see for yourself', ['#PizzaCheck', '#USPizza', '#FoodTok'], 'This is what a pizza should look like.'],
        ['No-plan Friday? Pizza has entered the chat', 'Best unplanned evenings always end up with pizza. What is your go-to order when you cannot decide anything else?', 'Tell us your go-to', ['#FridayVibes', '#USPizza', '#PizzaTok'], 'No plans? No problem. Pizza solves everything.'],
    ];

    public function generate(array $campaign): array
    {
        $posts = [];
        $dayIndex = 0;
        foreach (CarbonPeriod::create($campaign['start_date'], $campaign['end_date']) as $day) {
            foreach ($campaign['platforms'] as $platform) {
                $idx = $dayIndex % 7;
                [$idea, $caption, $cta, $hashtags, $hook] = match ($platform) {
                    'Facebook' => [...$this->facebook[$idx], [], ''],
                    'TikTok'   => $this->tiktok[$idx],
                    default    => $this->instagram[$idx], // Instagram
                };
                $posts[] = [
                    'date' => $day->toDateString(),
                    'platform' => $platform,
                    'content_type' => $platform === 'TikTok' ? 'Short video' : ($platform === 'Facebook' ? 'Community' : 'Photo / Carousel'),
                    'post_idea' => $idea,
                    'hook' => $hook,
                    'caption' => $caption,
                    'call_to_action' => $cta,
                    'hashtags' => $hashtags,
                    'suggested_time' => $day->isWeekend() ? '12:00' : '17:00',
                    'reason' => 'Sample suggestion: this is fictional demo content for demonstration purposes only, not based on real audience analytics.',
                ];
            }
            $dayIndex++;
        }

        return $posts;
    }
}
