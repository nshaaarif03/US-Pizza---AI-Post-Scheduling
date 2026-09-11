<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $counts   = Post::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $due      = Post::with('campaign')->where('status', 'Scheduled')
            ->where(function ($q) {
                $q->where('scheduled_date', '<', today()->toDateString())
                  ->orWhere(fn ($q) => $q->where('scheduled_date', today()->toDateString())->where('scheduled_time', '<', now()->format('H:i:s')));
            })->orderBy('scheduled_date')->orderBy('scheduled_time')->get();

        return view('dashboard', [
            'counts'   => $counts,
            'total'    => $counts->sum(),
            'upcoming' => Post::with('campaign')->upcoming()->limit(5)->get(),
            'recent'   => Post::with('campaign')->latest('id')->limit(4)->get(),
            'due'      => $due,
        ]);
    }
}
