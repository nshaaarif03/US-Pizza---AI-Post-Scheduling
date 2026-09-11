@extends('layouts.app')
@section('title','Content Library')
@section('content')
<div class="page-heading"><div><p class="eyebrow">FROM IDEA TO APPROVAL</p><h1>Content Library<span class="title-dot">.</span></h1><p class="muted mt-2">Review, refine, and make every post your own.</p></div><a class="btn btn-primary" href="{{ route('campaigns.create') }}"><x-icon name="plus"/>Generate New Content</a></div>
<form action="{{ route('posts.index') }}" method="get" class="filter-bar">
@if(request('campaign'))<input type="hidden" name="campaign" value="{{ request('campaign') }}"><span class="badge badge-approved">Campaign #{{ request('campaign') }}</span>@endif
<label>Status<select name="status"><option value="">All statuses</option>@foreach(\App\Models\Post::STATUSES as $status)<option @selected(request('status') === $status)>{{ $status }}</option>@endforeach</select></label>
<label>Platform<select name="platform"><option value="">All platforms</option>@foreach(\App\Models\Post::PLATFORMS as $platform)<option @selected(request('platform') === $platform)>{{ $platform }}</option>@endforeach</select></label><button class="btn btn-secondary">Apply filters</button><a class="text-link" href="{{ route('posts.index') }}">Reset</a><span class="muted text-sm sm:ml-auto">{{ $posts->total() }} posts</span></form>
<div class="notice notice-neutral"><x-icon name="spark"/><span>AI Generated Recommendation — Review Required. Check all claims, promotion details, and brand voice before approving.</span></div>
<div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-5">@forelse($posts as $post)@include('posts.card')@empty<div class="panel empty-state col-span-full"><x-icon name="file"/><h3>No posts found</h3><p>Try a different filter or generate your first content plan.</p><a class="btn btn-primary" href="{{ route('campaigns.create') }}">Generate Content</a></div>@endforelse</div><div class="mt-6">{{ $posts->links() }}</div>
@endsection
