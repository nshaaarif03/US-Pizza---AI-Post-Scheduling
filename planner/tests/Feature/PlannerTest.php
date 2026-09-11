<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\Post;
use App\Services\SamplePlanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PlannerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Http::preventStrayRequests();
        config(['services.gemini.key' => 'test-only-key']);
    }

    private function brief(array $overrides = []): array
    {
        return array_replace([
            'campaign_goal' => 'Bring people together', 'target_audience' => 'Students', 'product_promotion' => 'Pizza night',
            'tone' => 'Fun', 'platforms' => ['Instagram'], 'start_date' => today()->toDateString(),
            'end_date' => today()->addDays(2)->toDateString(), 'mode' => 'gemini',
        ], $overrides);
    }

    private function draft(string $status = 'Draft'): Post
    {
        $campaign = Campaign::create(collect($this->brief())->except('mode')->all());

        return $campaign->posts()->create([
            'platform' => 'Instagram', 'content_type' => 'Community', 'post_idea' => 'Pizza night', 'caption' => 'Good company and a shared pizza.',
            'call_to_action' => 'Tag your crew', 'hashtags' => ['#PizzaNight'], 'ai_reason' => 'Suggested early evening timing.',
            'planned_date' => today()->addDay(), 'suggested_time' => '17:00', 'status' => $status,
        ]);
    }

    private function fakePlan(?array $posts = null): void
    {
        $posts ??= app(SamplePlanService::class)->generate($this->brief());
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['candidates' => [['finishReason' => 'STOP', 'content' => ['parts' => [['text' => json_encode(['posts' => $posts])]]]]]])]);
    }

    public function test_all_screens_render_with_sample_data(): void
    {
        $this->seed();
        foreach (['/', '/campaigns/create', '/posts', '/calendar', '/scheduled', '/settings'] as $url) {
            $this->get($url)->assertOk();
        }
        foreach (Post::all() as $post) {
            $this->get('/posts/'.$post->id)->assertOk()->assertSee('Post preview');
        }
        $this->get('/')->assertSee('08')->assertSee('Sample');
    }

    public function test_gemini_generation_stores_only_drafts_and_uses_header_key(): void
    {
        $this->fakePlan();
        $this->post('/campaigns', $this->brief())->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseCount('campaigns', 1);
        $this->assertDatabaseCount('posts', 3);
        $this->assertSame(['Draft'], Post::pluck('status')->unique()->all());
        $this->assertSame(0, Post::whereNotNull('scheduled_date')->count());
        $this->assertFalse(Campaign::first()->is_demo);
        Http::assertSent(fn ($request) => $request->hasHeader('x-goog-api-key', 'test-only-key') && ! str_contains($request->url(), 'test-only-key') && $request['generationConfig']['responseMimeType'] === 'application/json');
        $this->assertSame('Connected', Cache::get('gemini.status'));
    }

    public function test_sample_mode_is_explicit_and_makes_no_api_request(): void
    {
        $this->post('/campaigns', $this->brief(['mode' => 'sample', 'platforms' => Post::PLATFORMS]))->assertSessionHasNoErrors();
        $this->assertDatabaseCount('posts', 9);
        $this->assertTrue(Campaign::first()->is_demo);
        Http::assertNothingSent();
    }

    public function test_missing_key_does_not_silently_fall_back_to_demo(): void
    {
        config(['services.gemini.key' => null]);
        $this->post('/campaigns', $this->brief())->assertSessionHasErrors('generation');
        $this->assertDatabaseCount('campaigns', 0);
        Http::assertNothingSent();
        $this->get('/settings')->assertSee('Not Configured');
    }

    public function test_rate_limit_and_connection_errors_save_nothing(): void
    {
        Http::fake(['*' => Http::response([], 429)]);
        $this->post('/campaigns', $this->brief())->assertSessionHasErrors('generation');
        Http::fake(['*' => Http::failedConnection()]);
        $this->post('/campaigns', $this->brief())->assertSessionHasErrors('generation');
        $this->assertDatabaseCount('posts', 0);
        $this->assertDatabaseCount('campaigns', 0);
    }

    public function test_invalid_json_empty_and_truncated_responses_are_rejected(): void
    {
        foreach ([['STOP', 'not json'], ['STOP', ''], ['MAX_TOKENS', '{}']] as [$reason,$text]) {
            $candidate = ['finishReason' => $reason, 'content' => ['parts' => [['text' => $text]]]];
            Http::fake(['*' => Http::response(['candidates' => [$candidate]])]);
            $this->post('/campaigns', $this->brief())->assertSessionHasErrors('generation');
        }
        $this->assertDatabaseCount('campaigns', 0);
    }

    public function test_invalid_platform_dates_and_duplicate_ai_posts_are_rejected(): void
    {
        $valid = app(SamplePlanService::class)->generate($this->brief());
        foreach (['platform', 'date', 'duplicate', 'date-array'] as $case) {
            $posts = $valid;
            if ($case === 'date-array') {
                $posts[0]['date'] = ['invalid'];
            }
            if ($case === 'platform') {
                $posts[0]['platform'] = 'LinkedIn';
            }
            if ($case === 'date') {
                $posts[0]['date'] = today()->subDay()->toDateString();
            }
            if ($case === 'duplicate') {
                $posts[1] = $posts[0];
            }
            $this->fakePlan($posts);
            $this->post('/campaigns', $this->brief())->assertSessionHasErrors('generation');
        }
        $this->assertDatabaseCount('posts', 0);
    }

    public function test_brief_validation_rejects_long_range_and_malformed_dates(): void
    {
        $this->post('/campaigns', $this->brief(['end_date' => today()->addDays(14)->toDateString()]))->assertSessionHasErrors('end_date');
        $this->post('/campaigns', $this->brief(['start_date' => ['invalid']]))->assertSessionHasErrors('start_date');
        Http::assertNothingSent();
    }

    public function test_draft_cannot_schedule_or_handoff_but_approved_can_schedule(): void
    {
        $post = $this->draft();
        $schedule = ['version' => 1, 'scheduled_date' => today()->addDay()->toDateString(), 'scheduled_time' => '17:00'];
        $this->post("/posts/$post->id/schedule", $schedule)->assertSessionHasErrors('post');
        $this->post("/posts/$post->id/platform", ['platform' => 'Instagram'])->assertForbidden();
        $this->post("/posts/$post->id/approve", ['version' => 1])->assertRedirect();
        $this->assertSame('Approved', $post->fresh()->status);
        $this->post("/posts/$post->id/schedule", array_replace($schedule, ['version' => 2]))->assertRedirect();
        $this->assertSame('Scheduled', $post->fresh()->status);
        $this->get('/scheduled')->assertSee('Pizza night');
        $this->delete("/posts/$post->id/schedule", ['version' => 3])->assertRedirect();
        $this->assertSame('Approved', $post->fresh()->status);
        $this->assertNull($post->fresh()->scheduled_date);
    }

    public function test_stale_versions_past_schedules_and_invalid_transitions_are_rejected(): void
    {
        $post = $this->draft('Approved');
        $this->post("/posts/$post->id/schedule", ['version' => 1, 'scheduled_date' => today()->subDay()->toDateString(), 'scheduled_time' => '17:00'])->assertSessionHasErrors('scheduled_date');
        $this->post("/posts/$post->id/approve", ['version' => 1])->assertSessionHasErrors('post');
        $this->post("/posts/$post->id/schedule", ['version' => 9, 'scheduled_date' => today()->addDay()->toDateString(), 'scheduled_time' => '17:00'])->assertSessionHasErrors('post');
        $this->delete("/posts/$post->id", ['version' => 1])->assertSessionHasErrors('post');
        $this->assertSame('Approved', $post->fresh()->status);
    }

    public function test_edit_requires_fresh_approval_and_escapes_output(): void
    {
        $post = $this->draft('Approved');
        $data = ['version' => 1, 'platform' => 'Instagram', 'post_idea' => '<script>alert(1)</script>', 'caption' => 'Changed caption', 'hook' => '', 'call_to_action' => 'Tag a friend', 'hashtags' => '#PizzaNight #Friends', 'planned_date' => today()->addDay()->toDateString(), 'suggested_time' => '18:00', 'status' => 'Scheduled'];
        $this->put("/posts/$post->id", $data)->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('Draft', $post->fresh()->status);
        $this->assertSame(2, $post->fresh()->version);
        $this->get("/posts/$post->id")->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        $this->post("/posts/$post->id/approve", ['version' => 1])->assertSessionHasErrors('post');
    }

    public function test_platform_actions_never_fake_publishing_or_change_status(): void
    {
        $post = $this->draft('Approved');
        foreach (Post::PLATFORMS as $platform) {
            $this->post("/posts/$post->id/platform", ['platform' => $platform])->assertSessionHas('handoff.connected', false);
            $this->get("/posts/$post->id")->assertSee("$platform publishing integration is not connected yet.")->assertSee("Open $platform");
            $this->assertSame('Approved', $post->fresh()->status);
        }
    }

    public function test_calendar_uses_confirmed_schedule_date_and_dashboard_limits_upcoming(): void
    {
        $post = $this->draft('Scheduled');
        $post->update(['scheduled_date' => today()->addDays(3), 'scheduled_time' => '18:00']);
        for ($i = 0; $i < 6; $i++) {
            $copy = $post->replicate();
            $copy->post_idea = 'Upcoming '.$i;
            $copy->scheduled_date = today()->addDays(4 + $i);
            $copy->save();
        }
        $this->get('/')->assertViewHas('upcoming', fn ($posts) => $posts->count() === 5 && $posts->first()->id === $post->id);
        $this->get('/calendar?month='.today()->addDays(3)->format('Y-m'))->assertViewHas('posts', fn ($posts) => $posts->has(today()->addDays(3)->toDateString()));
        $this->get('/calendar?month=garbage')->assertSessionHasErrors('month');
    }
}
