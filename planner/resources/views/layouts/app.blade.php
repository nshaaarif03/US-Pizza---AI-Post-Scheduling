<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="AI Social Media Planner. Plan, review and schedule your marketing content.">
    <meta name="color-scheme" content="light">
    <title>@yield('title', 'Dashboard') · AI Social Media Planner</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<a href="#main" class="skip-link">Skip to content</a>
<div class="mobile-bar lg:hidden"><x-brand/><button class="icon-button" id="menu-open" aria-label="Open navigation" aria-expanded="false" aria-controls="sidebar"><x-icon name="menu"/></button></div>
<button id="nav-overlay" class="nav-overlay hidden" aria-label="Close navigation" tabindex="-1"></button>
<aside id="sidebar" class="sidebar" aria-label="Main navigation">
    <div class="flex items-start justify-between"><x-brand/><button class="icon-button lg:hidden" id="menu-close" aria-label="Close navigation"><x-icon name="close"/></button></div>
    <div class="workspace-label"><span class="workspace-monogram">M</span><div><strong>Marketing team</strong><small>AI Social Media Planner</small></div></div>
    <p class="nav-heading">WORKSPACE</p>
    <nav>
        @foreach([['dashboard','grid','Dashboard'],['campaigns.index','spark','Campaigns'],['campaigns.create','plus','AI Content Generator'],['posts.index','file','Content Library'],['calendar','calendar','Content Calendar'],['scheduled.index','clock','Scheduled Posts']] as [$route,$icon,$label])
        <a href="{{ route($route) }}" @class(['nav-link','active' => request()->routeIs($route) || ($route === 'posts.index' && request()->routeIs('posts.*')) || ($route === 'campaigns.index' && request()->routeIs('campaigns.*') && !request()->routeIs('campaigns.create'))]) @if(request()->routeIs($route)) aria-current="page" @endif><x-icon :name="$icon"/>{{ $label }}</a>
        @endforeach
    </nav>
    <div class="sidebar-bottom">
        <div class="review-note"><x-icon name="spark"/><strong>A little AI. Your judgment.</strong><p>Every great post starts with a plan and a human review.</p></div>
        <a href="{{ route('settings') }}" @class(['nav-link','active'=>request()->routeIs('settings')])><x-icon name="settings"/>Settings</a>
        <div class="prototype-note">INTERNAL PROTOTYPE <span>V1.0</span></div>
    </div>
</aside>
<div class="app-shell">
    <header class="topbar"><div class="breadcrumb">Workspace <span>/</span> <strong>@yield('title', 'Dashboard')</strong></div><div class="flex items-center gap-3"><span class="timezone hidden sm:inline">Malaysia time · MYT</span><span class="avatar" aria-label="Marketing workspace">MT</span></div></header>
    <main id="main" class="main-content" tabindex="-1">
        @if(session('success'))<div class="notice notice-success" role="status"><x-icon name="check"/><span>{{ session('success') }}</span></div>@endif
        @if($errors->any())<div class="notice notice-error" role="alert"><x-icon name="info"/><div><strong>Please check the following</strong><ul class="list-disc pl-5 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>@endif
        @yield('content')
        <footer class="page-footer"><span>AI Social Media Planner <span class="mx-2">/</span> US Pizza marketing prototype</span><span>Plan with AI. Publish with care.</span></footer>
    </main>
</div>
<div id="toast" class="toast hidden" role="status" aria-live="polite"></div>
</body>
</html>
