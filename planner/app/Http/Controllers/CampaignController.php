<?php

namespace App\Http\Controllers;

use App\Http\Requests\CampaignRequest;
use App\Models\Campaign;
use App\Services\GeminiService;
use App\Services\SamplePlanService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = Campaign::withCount([
            'posts',
            'posts as approved_count' => fn ($q) => $q->where('status', 'Approved'),
            'posts as scheduled_count' => fn ($q) => $q->where('status', 'Scheduled'),
            'posts as posted_count'    => fn ($q) => $q->where('status', 'Posted'),
        ])->latest()->paginate(12);

        return view('campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        return view('campaigns.create');
    }

    public function store(CampaignRequest $request, GeminiService $gemini, SamplePlanService $sample)
    {
        $data = $request->safe()->except('mode');
        $data['is_demo'] = $request->input('mode') === 'sample';
        try {
            $posts = $data['is_demo'] ? $sample->generate($data) : $gemini->generate($data);
        } catch (RuntimeException $e) {
            return back()->withInput()->withErrors(['generation' => $e->getMessage()]);
        }
        $campaign = DB::transaction(function () use ($data, $posts) {
            $campaign = Campaign::create($data);
            foreach ($posts as $post) {
                $campaign->posts()->create([
                    'platform' => $post['platform'], 'content_type' => $post['content_type'], 'post_idea' => $post['post_idea'],
                    'hook' => $post['hook'], 'caption' => $post['caption'], 'call_to_action' => $post['call_to_action'],
                    'hashtags' => $post['hashtags'], 'ai_reason' => $post['reason'], 'planned_date' => $post['date'],
                    'suggested_time' => $post['suggested_time'], 'status' => 'Draft',
                ]);
            }

            return $campaign;
        });

        return to_route('posts.index', ['campaign' => $campaign->id])->with('success', $data['is_demo'] ? 'Sample plan created. All posts are drafts for review.' : 'Content generated successfully. All posts are drafts for review.');
    }
}
