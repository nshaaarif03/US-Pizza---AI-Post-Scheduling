<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CalendarController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validate(['month' => ['bail', 'nullable', 'string', 'date_format:Y-m', 'after_or_equal:2020-01', 'before_or_equal:2100-12']]);
        $month = CarbonImmutable::createFromFormat('!Y-m', $data['month'] ?? now()->format('Y-m'));
        $start = $month->startOfMonth()->startOfWeek();
        $end = $month->endOfMonth()->endOfWeek();
        $posts = Post::with('campaign')->whereBetween(DB::raw('COALESCE(scheduled_date, planned_date)'), [$start->toDateString(), $end->toDateString()])->orderByRaw('COALESCE(scheduled_time, suggested_time)')->get()->groupBy(fn ($p) => ($p->scheduled_date ?? $p->planned_date)->toDateString());

        return view('calendar', compact('month', 'start', 'end', 'posts'));
    }
}
