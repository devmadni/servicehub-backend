@extends('layouts.app')
@section('title', 'Disputes')
@section('heading', 'Disputes')
@section('subheading', 'Monitor and resolve escalated disputes')

@section('content')
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
    @if($disputes->isEmpty())
        <div class="px-6 py-16 text-center">
            <div class="w-11 h-11 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="text-sm text-slate-500">No disputes to review.</div>
        </div>
    @else
        <div class="divide-y divide-slate-100">
            @foreach($disputes as $dispute)
                <div class="px-6 py-4 flex items-start gap-4">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5
                        {{ $dispute->human_flag ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">
                        S{{ $dispute->stage }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-sm font-semibold text-slate-800">{{ ucfirst(str_replace('_', ' ', $dispute->trigger_type)) }}</span>
                            @if($dispute->human_flag)
                                <span class="text-[10px] font-semibold bg-red-100 text-red-700 ring-1 ring-red-200 px-2 py-0.5 rounded-full">Human review needed</span>
                            @endif
                        </div>
                        <div class="text-xs text-slate-400 mt-1">
                            {{ $dispute->user?->name }}
                            <span class="text-slate-300 mx-1">·</span>
                            Booking {{ $dispute->booking?->booking_ref }}
                        </div>
                        <div class="text-xs text-slate-400 mt-0.5 line-clamp-1">{{ $dispute->description }}</div>
                    </div>
                    <div class="flex-shrink-0 text-right">
                        @if($dispute->outcome)
                            <span class="text-xs font-medium bg-teal-50 text-teal-700 ring-1 ring-teal-200 px-2.5 py-1 rounded-full">
                                {{ ucfirst($dispute->outcome) }}
                            </span>
                        @else
                            <span class="text-xs font-medium bg-amber-50 text-amber-700 ring-1 ring-amber-200 px-2.5 py-1 rounded-full">
                                Open
                            </span>
                        @endif
                        <div class="text-[11px] text-slate-400 mt-1.5">{{ $dispute->created_at?->diffForHumans() }}</div>
                    </div>
                </div>
            @endforeach
        </div>
        @if($disputes->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">{{ $disputes->links() }}</div>
        @endif
    @endif
</div>
@endsection
