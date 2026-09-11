@extends('layouts.app')
@section('title','Campaigns')
@section('content')
<div class="page-heading"><div><p class="eyebrow">YOUR CREATIVE HISTORY</p><h1>Campaigns<span class="title-dot">.</span></h1><p class="muted mt-2">Review every content plan you have generated.</p></div><a href="{{ route('campaigns.create') }}" class="btn btn-primary"><x-icon name="plus"/>New Campaign</a></div>
<section class="panel">
<div class="section-heading"><h2>All campaigns <span class="count-pill">{{ $campaigns->total() }}</span></h2><span class="muted text-sm">Most recent first</span></div>
<div class="table-wrap"><table><thead><tr><th>Campaign</th><th>Platforms</th><th>Duration</th><th>Posts</th><th>Progress</th><th><span class="sr-only">Open</span></th></tr></thead><tbody>
@forelse($campaigns as $campaign)
<tr>
<td>
  <div><a class="row-title" href="{{ route('posts.index',['campaign'=>$campaign->id]) }}">{{ $campaign->campaign_goal }}</a>@if($campaign->is_demo)<span class="sample-label">SAMPLE</span>@endif</div>
  <small>{{ $campaign->tone }} tone · {{ $campaign->target_audience }}</small>
</td>
<td>
  <div class="flex flex-wrap gap-1">
    @foreach($campaign->platforms as $p)<x-platform :platform="$p" :label="false"/>@endforeach
  </div>
</td>
<td class="whitespace-nowrap">
  {{ $campaign->start_date->format('d M Y') }}<small>to {{ $campaign->end_date->format('d M Y') }}</small>
</td>
<td>
  <span class="font-semibold">{{ $campaign->posts_count }}</span><small>posts</small>
</td>
<td>
  @php
    $total = $campaign->posts_count;
    $approved = $campaign->approved_count + $campaign->scheduled_count + $campaign->posted_count;
    $pct = $total > 0 ? round($approved / $total * 100) : 0;
  @endphp
  <div class="progress-wrap">
    <div class="progress-bar" style="width:{{ $pct }}%"></div>
  </div>
  <small>{{ $approved }}/{{ $total }} approved</small>
</td>
<td><a href="{{ route('posts.index',['campaign'=>$campaign->id]) }}" class="icon-button" aria-label="View posts for {{ $campaign->campaign_goal }}"><x-icon name="chevron"/></a></td>
</tr>
@empty
<tr><td colspan="6"><div class="empty-state"><x-icon name="spark"/><h3>No campaigns yet</h3><p>Start by generating your first AI content plan.</p><a class="btn btn-primary" href="{{ route('campaigns.create') }}">Generate Content</a></div></td></tr>
@endforelse
</tbody></table></div>
</section>
<div class="mt-6">{{ $campaigns->links() }}</div>
@endsection
