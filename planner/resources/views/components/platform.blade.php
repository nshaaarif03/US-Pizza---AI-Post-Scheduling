@props(['platform', 'label' => true])
<span class="platform"><span class="platform-icon platform-{{ strtolower($platform) }}"><x-icon :name="$platform" /></span>@if($label)<span>{{ $platform }}</span>@endif</span>
