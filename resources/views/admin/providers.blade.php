@extends('layouts.app')
@section('title', 'Manage Providers')
@section('heading', 'Providers')
@section('subheading', $providers->total() . ' total providers')

@section('content')
<!-- Filter tabs -->
<div class="flex gap-1.5 mb-5 flex-wrap">
    @foreach(['all' => 'All', 'active' => 'Active', 'suspended' => 'Suspended', 'blacklisted' => 'Blacklisted'] as $val => $label)
        <a href="{{ route('admin.providers', ['status' => $val]) }}"
           class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all
                  {{ (request('status', 'all') === $val)
                      ? 'bg-slate-900 text-white shadow-sm'
                      : 'bg-white text-slate-500 border border-slate-200 hover:border-slate-300 hover:text-slate-700' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
    <div class="divide-y divide-slate-100">
        @forelse($providers as $provider)
            <div class="px-6 py-4 flex items-center gap-4">
                <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center text-sm font-bold text-slate-500 flex-shrink-0">
                    {{ strtoupper(substr($provider->name, 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-semibold text-slate-800">{{ $provider->name }}</div>
                    <div class="text-xs text-slate-400 mt-0.5">
                        {{ $provider->area }}
                        <span class="text-slate-300 mx-1">·</span>
                        ⭐ {{ $provider->rating_avg }}
                        <span class="text-slate-300 mx-1">·</span>
                        On-time: {{ $provider->on_time_score }}%
                        @if($provider->warning_count > 0)
                            <span class="text-slate-300 mx-1">·</span>
                            <span class="text-amber-600 font-medium">⚠ {{ $provider->warning_count }} warnings</span>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-shrink-0">
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium ring-1
                        {{ $provider->status === 'active'
                            ? 'bg-teal-50 text-teal-700 ring-teal-200'
                            : ($provider->status === 'blacklisted'
                                ? 'bg-red-50 text-red-700 ring-red-200'
                                : 'bg-amber-50 text-amber-700 ring-amber-200') }}">
                        {{ ucfirst($provider->status) }}
                    </span>

                    <!-- Actions dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                                class="flex items-center gap-1 text-xs font-medium border border-slate-200 text-slate-600 px-2.5 py-1.5 rounded-lg hover:bg-slate-50 transition-colors">
                            Actions
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open" x-cloak @click.outside="open = false"
                             class="absolute right-0 top-full mt-1 w-36 bg-white border border-slate-200 rounded-xl shadow-lg z-20 overflow-hidden py-1">
                            @foreach(['active' => 'Set Active', 'suspended' => 'Suspend', 'blacklisted' => 'Blacklist'] as $status => $label)
                                @if($provider->status !== $status)
                                    <form method="POST" action="{{ route('admin.providers.status', $provider) }}">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="status" value="{{ $status }}">
                                        <button class="w-full text-left px-3.5 py-2 text-xs font-medium hover:bg-slate-50 transition-colors
                                            {{ $status === 'blacklisted' ? 'text-red-600' : 'text-slate-700' }}">
                                            {{ $label }}
                                        </button>
                                    </form>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="px-6 py-16 text-center text-sm text-slate-400">No providers found.</div>
        @endforelse
    </div>
    @if($providers->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">{{ $providers->links() }}</div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endsection
