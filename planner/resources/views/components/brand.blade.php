<a href="{{ route('dashboard') }}" class="brand" aria-label="US Pizza AI Social Media Planner dashboard">
    @if(file_exists(public_path('images/us-pizza-logo.png')))
        <img src="{{ asset('images/us-pizza-logo.png') }}" alt="US Pizza" class="h-14 w-32 object-contain object-left">
    @else
        <span class="brand-wordmark" title="Temporary text wordmark — replace with supplied logo">US<span>PIZZA</span></span>
    @endif
    <span class="brand-caption">MARKETING WORKSPACE</span>
</a>
