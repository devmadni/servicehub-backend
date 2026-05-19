@extends('layouts.app')
@section('title', 'My Profile')
@section('heading', 'Provider Profile')
@section('subheading', $provider->name)

@section('content')
<div class="max-w-2xl space-y-5">

    <!-- Key stats -->
    <div class="grid grid-cols-3 gap-4">
        @foreach([
            ['Rating',      number_format($provider->rating_avg, 2) . ' ★', 'bg-amber-100',   'text-amber-700'],
            ['On-time',     $provider->on_time_score . '%',                  'bg-teal-100',    'text-teal-700'],
            ['Cancel rate', $provider->cancel_rate . '%',                    'bg-red-100',     'text-red-700'],
        ] as [$label, $val, $bg, $text])
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 text-center">
                <div class="text-xl font-bold text-slate-900 {{ $text }}">{{ $val }}</div>
                <div class="text-xs text-slate-500 font-medium mt-1">{{ $label }}</div>
            </div>
        @endforeach
    </div>

    <!-- Profile details -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm divide-y divide-slate-100">
        @foreach([
            ['Business Name',  $provider->name],
            ['Area',           $provider->area],
            ['Base rate',      'PKR ' . number_format($provider->price_min)],
            ['Experience',     $provider->experience_years . ' years'],
            ['Status',         ucfirst($provider->status)],
        ] as [$label, $value])
            <div class="px-5 py-3.5 flex justify-between items-center">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wide">{{ $label }}</span>
                <span class="text-sm font-medium text-slate-800">{{ $value }}</span>
            </div>
        @endforeach
        @if(!empty($provider->specializations))
            <div class="px-5 py-4">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3">Specializations</div>
                <div class="flex flex-wrap gap-2">
                    @foreach($provider->specializations as $spec)
                        <span class="text-xs font-medium bg-teal-50 text-teal-700 border border-teal-200 px-2.5 py-1 rounded-lg">{{ $spec }}</span>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- Reputation history -->
    @if($reputations->isNotEmpty())
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="text-sm font-semibold text-slate-800">Reputation History</h3>
                <p class="text-xs text-slate-400 mt-0.5">Recent score changes</p>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($reputations as $rep)
                    <div class="px-5 py-3.5 flex justify-between items-center">
                        <span class="text-sm text-slate-600">{{ ucfirst($rep->trigger) }}</span>
                        <span class="text-sm font-semibold font-mono {{ $rep->delta >= 0 ? 'text-teal-600' : 'text-red-500' }}">
                            {{ $rep->delta >= 0 ? '+' : '' }}{{ number_format($rep->delta, 4) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
