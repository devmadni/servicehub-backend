@extends('layouts.app')
@section('title', 'Dashboard')
@section('heading', 'Dashboard')
@section('subheading', 'Welcome back, ' . auth()->user()->name)

@section('header-actions')
    <a href="{{ route('user.request') }}"
       class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-500 active:bg-teal-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition-all shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        New Request
    </a>
@endsection

@section('content')

<!-- Stats -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @php
        $statItems = [
            ['label' => 'Total Bookings',  'value' => $stats['total'],     'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'color' => 'teal'],
            ['label' => 'Confirmed',       'value' => $stats['confirmed'], 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'blue'],
            ['label' => 'Completed',       'value' => $stats['completed'], 'icon' => 'M5 13l4 4L19 7', 'color' => 'green'],
            ['label' => 'Open Disputes',   'value' => $stats['disputes'],  'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z', 'color' => 'red'],
        ];
        $colorMap = [
            'teal'  => ['icon_bg' => 'bg-teal-100',  'icon_text' => 'text-teal-600',  'value_text' => 'text-teal-700'],
            'blue'  => ['icon_bg' => 'bg-blue-100',  'icon_text' => 'text-blue-600',  'value_text' => 'text-blue-700'],
            'green' => ['icon_bg' => 'bg-emerald-100','icon_text' => 'text-emerald-600','value_text' => 'text-emerald-700'],
            'red'   => ['icon_bg' => 'bg-red-100',   'icon_text' => 'text-red-600',   'value_text' => 'text-red-700'],
        ];
    @endphp
    @foreach($statItems as $s)
        @php $c = $colorMap[$s['color']]; @endphp
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <div class="text-2xl font-bold text-slate-900">{{ $s['value'] }}</div>
                    <div class="text-xs text-slate-500 mt-1 font-medium">{{ $s['label'] }}</div>
                </div>
                <div class="w-9 h-9 {{ $c['icon_bg'] }} rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 {{ $c['icon_text'] }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $s['icon'] }}"/>
                    </svg>
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- Recent bookings -->
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-slate-800">Recent Bookings</h2>
        <a href="{{ route('user.bookings') }}" class="text-xs text-teal-600 hover:text-teal-700 font-medium">View all →</a>
    </div>
    @if($bookings->isEmpty())
        <div class="px-6 py-16 text-center">
            <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="text-sm font-medium text-slate-600 mb-1">No bookings yet</div>
            <div class="text-xs text-slate-400 mb-4">Describe your service need and let AI find the best providers.</div>
            <a href="{{ route('user.request') }}"
               class="inline-flex items-center gap-1.5 text-sm font-medium text-teal-600 hover:text-teal-700">
                Make your first request
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </a>
        </div>
    @else
        <div class="divide-y divide-slate-100">
            @foreach($bookings as $booking)
                <a href="{{ route('user.bookings.show', $booking) }}"
                   class="flex items-center gap-4 px-6 py-3.5 hover:bg-slate-50 transition-colors group">
                    <div class="w-9 h-9 bg-slate-100 rounded-xl flex items-center justify-center text-[11px] font-mono font-semibold text-slate-500 flex-shrink-0">
                        {{ substr($booking->booking_ref, -3) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-slate-800">{{ $booking->serviceRequest?->service_type ?? 'Service' }}</div>
                        <div class="text-xs text-slate-400 mt-0.5">
                            {{ $booking->provider?->name }} · {{ $booking->slot_datetime?->format('M j, h:i A') }}
                        </div>
                    </div>
                    <div class="flex items-center gap-3 flex-shrink-0">
                        <x-status-badge :status="$booking->status" />
                        <svg class="w-4 h-4 text-slate-300 group-hover:text-slate-400 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
