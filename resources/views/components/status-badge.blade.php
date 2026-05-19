@props(['status', 'size' => 'sm'])

@php
$configs = [
    'pending'   => ['bg-amber-50 text-amber-700 ring-1 ring-amber-200',  '●'],
    'confirmed' => ['bg-blue-50 text-blue-700 ring-1 ring-blue-200',     '●'],
    'en_route'  => ['bg-violet-50 text-violet-700 ring-1 ring-violet-200','●'],
    'completed' => ['bg-teal-50 text-teal-700 ring-1 ring-teal-200',     '●'],
    'disputed'  => ['bg-red-50 text-red-700 ring-1 ring-red-200',        '●'],
    'cancelled' => ['bg-slate-100 text-slate-500 ring-1 ring-slate-200', '●'],
];
[$cls, $dot] = $configs[$status] ?? ['bg-slate-100 text-slate-500 ring-1 ring-slate-200', '●'];
$padding = $size === 'lg' ? 'px-3 py-1 text-sm gap-1.5' : 'px-2 py-0.5 text-xs gap-1';
$dotSize = $size === 'lg' ? 'text-[8px]' : 'text-[6px]';
@endphp

<span class="{{ $cls }} {{ $padding }} inline-flex items-center rounded-full font-medium capitalize">
    <span class="{{ $dotSize }}">{{ $dot }}</span>
    {{ str_replace('_', ' ', $status) }}
</span>
