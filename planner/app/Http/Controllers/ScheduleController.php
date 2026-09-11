<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\PostWorkflow;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ScheduleController extends Controller
{
    public function index()
    {
        $posts = Post::with('campaign')->where('status', 'Scheduled')->orderBy('scheduled_date')->orderBy('scheduled_time')->paginate(15);

        return view('schedule.index', compact('posts'));
    }

    public function store(Request $request, Post $post, PostWorkflow $workflow)
    {
        $data = $request->validate(['version' => ['required', 'integer', 'min:1'], 'scheduled_date' => ['bail', 'required', 'string', 'date_format:Y-m-d'], 'scheduled_time' => ['bail', 'required', 'string', 'date_format:H:i']]);
        $at = CarbonImmutable::parse($data['scheduled_date'].' '.$data['scheduled_time'], config('app.timezone'));
        if ($at->isPast()) {
            throw ValidationException::withMessages(['scheduled_date' => 'Choose a posting date and time in the future (Malaysia time).']);
        }
        $workflow->change($post, $request->integer('version'), ['Approved', 'Scheduled'], fn ($p) => $p->update(['scheduled_date' => $data['scheduled_date'], 'scheduled_time' => $data['scheduled_time'], 'status' => 'Scheduled']));

        return to_route('posts.show', $post)->with('success', 'Post scheduled for '.$at->format('j F Y').' at '.$at->format('g:i A').'.');
    }

    public function cancel(Request $request, Post $post, PostWorkflow $workflow)
    {
        $request->validate(['version' => ['required', 'integer', 'min:1']]);
        $workflow->change($post, $request->integer('version'), ['Scheduled'], fn ($p) => $p->update(['status' => 'Approved', 'scheduled_date' => null, 'scheduled_time' => null]));

        return to_route('posts.show', $post)->with('success', 'Schedule cancelled. Your content is still approved.');
    }
}
