@props(['name' => 'grid'])
@php
$paths = [
 'grid' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
 'spark' => '<path d="m12 3 2.7 6.3L21 12l-6.3 2.7L12 21l-2.7-6.3L3 12l6.3-2.7L12 3Z"/><path d="M20 2v4m-2-2h4"/>',
 'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18m-13 5h2m4 0h2"/>',
 'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
 'settings' => '<path d="m9 3-1 3-3 1-2 4 2 2v4l4 3 3-1 3 1 4-3v-4l2-2-2-4-3-1-1-3H9Z"/><circle cx="12" cy="12" r="3"/>',
 'file' => '<path d="M14 3H5v18h14V8l-5-5Zm0 0v5h5M8 12h8M8 16h6"/>',
 'check' => '<path d="m5 12 4 4L19 6"/>',
 'plus' => '<path d="M12 5v14M5 12h14"/>',
 'arrow' => '<path d="M5 12h14m-6-6 6 6-6 6"/>',
 'chevron' => '<path d="m9 5 7 7-7 7"/>',
 'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
 'close' => '<path d="m6 6 12 12M6 18 18 6"/>',
 'copy' => '<rect x="8" y="8" width="12" height="13" rx="2"/><path d="M16 8V3H3v13h5"/>',
 'image' => '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8" cy="8" r="1.5"/><path d="m21 16-6-6L3 21"/>',
 'info' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7v1"/>',
 'Instagram' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><path d="M17.5 6.5h.01"/>',
 'Facebook' => '<path d="M14 21v-8h3l1-4h-4V7c0-1 1-2 2-2h2V2h-3c-3 0-5 2-5 5v2H7v4h3v8"/>',
 'TikTok' => '<path d="M14 3v12a5 5 0 1 1-4-5v4a2 2 0 1 0 1 2V3h3Zm0 0c1 4 3 5 6 5v4c-3 0-5-2-6-3"/>',
];
@endphp
<svg {{ $attributes->class(['icon']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $paths[$name] ?? $paths['file'] !!}</svg>
