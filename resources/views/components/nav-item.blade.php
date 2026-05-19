@props(['href', 'label', 'icon' => 'grid', 'external' => false])

@php
$active = request()->url() === $href || (str_starts_with(request()->url(), $href . '/') && $href !== '/');

$icons = [
    'grid'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>',
    'calendar' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
    'users'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>',
    'alert'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>',
    'user'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>',
    'plus'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>',
    'book'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>',
];
@endphp

<a href="{{ $href }}" {{ $external ? 'target=_blank rel=noopener' : '' }}
   class="group flex items-center gap-2.5 px-3 py-2 rounded-lg text-[13px] font-medium transition-all duration-150
          {{ $active
              ? 'bg-teal-500/15 text-teal-300'
              : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60' }}">
    <svg class="w-[15px] h-[15px] flex-shrink-0 {{ $active ? 'text-teal-400' : 'text-slate-500 group-hover:text-slate-300' }}"
         fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        {!! $icons[$icon] ?? $icons['grid'] !!}
    </svg>
    <span>{{ $label }}</span>
    @if($external)
        <svg class="w-3 h-3 ml-auto opacity-40" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
        </svg>
    @endif
    @if($active)
        <span class="ml-auto w-1.5 h-1.5 rounded-full bg-teal-400"></span>
    @endif
</a>
