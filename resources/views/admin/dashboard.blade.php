@extends('layouts.app')
@section('title', 'Admin Dashboard')
@section('heading', 'Admin Dashboard')
@section('subheading', 'System overview · ' . now()->format('l, F j'))

@section('content')

<!-- Stats -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @php
        $cards = [
            ['Total Providers', $stats['providers'], 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z', 'teal',   route('admin.providers')],
            ['Total Bookings',  $stats['bookings'],  'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'blue',   route('admin.bookings')],
            ['Active Disputes', $stats['disputes'],  'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z', 'red',    route('admin.disputes')],
            ['Total Users',     $stats['users'],     'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'violet', '#'],
        ];
        $colorMap = [
            'teal'   => ['bg-teal-100',   'text-teal-600'],
            'blue'   => ['bg-blue-100',   'text-blue-600'],
            'red'    => ['bg-red-100',    'text-red-600'],
            'violet' => ['bg-violet-100', 'text-violet-600'],
        ];
    @endphp
    @foreach($cards as [$label, $value, $icon, $color, $link])
        @php [$iconBg, $iconText] = $colorMap[$color]; @endphp
        <a href="{{ $link }}" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 hover:border-slate-300 hover:shadow-md transition-all group">
            <div class="flex items-start justify-between">
                <div>
                    <div class="text-2xl font-bold text-slate-900">{{ $value }}</div>
                    <div class="text-xs text-slate-500 mt-1 font-medium">{{ $label }}</div>
                </div>
                <div class="w-9 h-9 {{ $iconBg }} rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                    <svg class="w-4 h-4 {{ $iconText }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/>
                    </svg>
                </div>
            </div>
        </a>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    <!-- Recent bookings -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-800">Recent Bookings</h2>
            <a href="{{ route('admin.bookings') }}" class="text-xs text-teal-600 hover:text-teal-700 font-medium">View all →</a>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($recentBookings as $booking)
                <div class="px-5 py-3.5 flex items-center gap-3">
                    <div class="flex-1 min-w-0 text-sm">
                        <span class="font-medium text-slate-800">{{ $booking->serviceRequest?->service_type }}</span>
                        <span class="text-slate-300 mx-1.5">·</span>
                        <span class="text-slate-500">{{ $booking->user?->name }}</span>
                    </div>
                    <x-status-badge :status="$booking->status" />
                </div>
            @empty
                <div class="px-5 py-10 text-center text-sm text-slate-400">No bookings yet.</div>
            @endforelse
        </div>
    </div>

    <!-- Provider status -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-800">Provider Status</h2>
            <a href="{{ route('admin.providers') }}" class="text-xs text-teal-600 hover:text-teal-700 font-medium">Manage →</a>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($topProviders as $provider)
                <div class="px-5 py-3.5 flex items-center gap-3">
                    <div class="w-8 h-8 bg-slate-100 rounded-lg flex items-center justify-center text-xs font-semibold text-slate-500 flex-shrink-0">
                        {{ strtoupper(substr($provider->name, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-slate-800 truncate">{{ $provider->name }}</div>
                        <div class="text-xs text-slate-400 mt-0.5">{{ $provider->area }} · ⭐ {{ $provider->rating_avg }}</div>
                    </div>
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium ring-1
                        {{ $provider->status === 'active'
                            ? 'bg-teal-50 text-teal-700 ring-teal-200'
                            : ($provider->status === 'blacklisted'
                                ? 'bg-red-50 text-red-700 ring-red-200'
                                : 'bg-amber-50 text-amber-700 ring-amber-200') }}">
                        {{ ucfirst($provider->status) }}
                    </span>
                </div>
            @empty
                <div class="px-5 py-10 text-center text-sm text-slate-400">No providers.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
