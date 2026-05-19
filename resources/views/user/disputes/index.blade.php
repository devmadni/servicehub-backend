@extends('layouts.app')
@section('title', 'Disputes')
@section('heading', 'My Disputes')

@section('content')
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
    @if($disputes->isEmpty())
        <div class="px-6 py-16 text-center">
            <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="text-sm font-medium text-slate-600 mb-1">No disputes</div>
            <div class="text-xs text-slate-400">Any disputes you open will appear here.</div>
        </div>
    @else
        <div class="divide-y divide-slate-100">
            @foreach($disputes as $dispute)
                <div class="px-6 py-4 flex items-center gap-4">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-xs font-bold flex-shrink-0
                        {{ $dispute->stage === 3 ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">
                        S{{ $dispute->stage }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold text-slate-800">{{ ucfirst(str_replace('_', ' ', $dispute->trigger_type)) }}</div>
                        <div class="text-xs text-slate-400 mt-0.5 truncate">{{ $dispute->description }}</div>
                    </div>
                    <div class="flex-shrink-0">
                        @if($dispute->outcome)
                            <span class="text-xs bg-teal-100 text-teal-700 ring-1 ring-teal-200 px-2.5 py-1 rounded-full font-medium">
                                {{ ucfirst($dispute->outcome) }}
                            </span>
                        @elseif($dispute->human_flag)
                            <span class="text-xs bg-red-100 text-red-700 ring-1 ring-red-200 px-2.5 py-1 rounded-full font-medium">
                                Human review
                            </span>
                        @else
                            <span class="text-xs bg-amber-100 text-amber-700 ring-1 ring-amber-200 px-2.5 py-1 rounded-full font-medium">
                                Stage {{ $dispute->stage }}
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
