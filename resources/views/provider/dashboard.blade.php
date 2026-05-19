@extends('layouts.app')
@section('title', 'Provider Dashboard')
@section('heading', 'Provider Dashboard')
@section('subheading', auth()->user()->name . ' · ' . ($provider->area ?? ''))

@section('content')

<!-- Stats -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @php
        $stats_list = [
            ['Active Bookings',  $stats['active'],                            'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'teal'],
            ['Completed',        $stats['completed'],                         'M5 13l4 4L19 7', 'green'],
            ['Rating',           number_format($provider->rating_avg, 1) . ' / 5', 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z', 'amber'],
            ['On-time Rate',     $provider->on_time_score . '%',              'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'blue'],
        ];
        $colorMap = [
            'teal'  => ['bg-teal-100',    'text-teal-600'],
            'green' => ['bg-emerald-100', 'text-emerald-600'],
            'amber' => ['bg-amber-100',   'text-amber-600'],
            'blue'  => ['bg-blue-100',    'text-blue-600'],
        ];
    @endphp
    @foreach($stats_list as [$label, $value, $icon, $color])
        @php [$iconBg, $iconText] = $colorMap[$color]; @endphp
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <div class="flex items-start justify-between">
                <div>
                    <div class="text-2xl font-bold text-slate-900">{{ $value }}</div>
                    <div class="text-xs text-slate-500 mt-1 font-medium">{{ $label }}</div>
                </div>
                <div class="w-9 h-9 {{ $iconBg }} rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 {{ $iconText }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/>
                    </svg>
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- Today's schedule -->
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
        <div>
            <h2 class="text-sm font-semibold text-slate-800">Today's Schedule</h2>
            <p class="text-xs text-slate-400 mt-0.5">{{ now()->format('l, F j') }}</p>
        </div>
        <a href="{{ route('provider.bookings') }}" class="text-xs text-teal-600 hover:text-teal-700 font-medium">All bookings →</a>
    </div>
    @if($todayBookings->isEmpty())
        <div class="px-6 py-14 text-center">
            <div class="w-11 h-11 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="text-sm text-slate-500">No bookings scheduled for today</div>
        </div>
    @else
        <div class="divide-y divide-slate-100">
            @foreach($todayBookings as $booking)
                <div class="px-6 py-4 flex items-center gap-4">
                    <div class="text-sm font-semibold text-slate-500 w-16 flex-shrink-0 font-mono">
                        {{ $booking->slot_datetime?->format('h:i A') }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-slate-800">{{ $booking->serviceRequest?->service_type }}</div>
                        <div class="text-xs text-slate-400 mt-0.5">
                            {{ $booking->user?->name }}
                            @if($booking->user?->phone)
                                <span class="text-slate-300 mx-1">·</span>
                                {{ $booking->user->phone }}
                            @endif
                        </div>
                    </div>
                    <x-status-badge :status="$booking->status" />
                    @if($booking->status === 'confirmed')
                        <form method="POST" action="{{ route('provider.bookings.status', $booking) }}">
                            @csrf @method('PUT')
                            <input type="hidden" name="status" value="en_route">
                            <button class="text-xs font-semibold bg-blue-100 text-blue-700 hover:bg-blue-200 px-3 py-1.5 rounded-lg transition-colors">
                                En Route
                            </button>
                        </form>
                    @elseif($booking->status === 'en_route')
                        <form method="POST" action="{{ route('provider.bookings.status', $booking) }}">
                            @csrf @method('PUT')
                            <input type="hidden" name="status" value="completed">
                            <button class="text-xs font-semibold bg-teal-100 text-teal-700 hover:bg-teal-200 px-3 py-1.5 rounded-lg transition-colors">
                                Complete
                            </button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
