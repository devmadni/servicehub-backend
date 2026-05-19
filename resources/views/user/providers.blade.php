@extends('layouts.app')
@section('title', 'Choose Provider')
@section('heading', 'Choose a Provider')
@section('subheading', 'AI-ranked by 10 factors for your request')

@section('content')
<div class="max-w-3xl space-y-4">

    <!-- Request summary -->
    <div class="flex items-center gap-3 p-4 bg-teal-50 border border-teal-200 rounded-2xl">
        <div class="w-7 h-7 bg-teal-500 rounded-lg flex items-center justify-center text-white text-xs font-bold flex-shrink-0">1</div>
        <div class="text-sm">
            <span class="font-semibold text-teal-900">{{ $serviceRequest->service_type }}</span>
            <span class="text-teal-700"> · {{ $serviceRequest->location }} · {{ ucfirst($serviceRequest->urgency) }} urgency · {{ ucfirst($serviceRequest->complexity) }}</span>
        </div>
        <span class="ml-auto text-xs font-semibold text-teal-700 bg-teal-100 px-2.5 py-1 rounded-full flex-shrink-0">
            {{ number_format($serviceRequest->confidence * 100) }}% confidence
        </span>
    </div>

    @forelse($providers as $i => $provider)
        <div class="bg-white rounded-2xl border {{ $i === 0 ? 'border-teal-300 shadow-teal-100' : 'border-slate-200' }} shadow-sm overflow-hidden transition-shadow hover:shadow-md">

            @if($i === 0)
                <div class="px-5 py-2 bg-gradient-to-r from-teal-500 to-teal-600 flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <span class="text-white text-xs font-semibold">Best Match</span>
                </div>
            @endif

            <div class="p-5">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="w-11 h-11 {{ $i === 0 ? 'bg-teal-100 text-teal-700' : 'bg-slate-100 text-slate-500' }} rounded-xl flex items-center justify-center text-sm font-bold flex-shrink-0">
                            #{{ $i + 1 }}
                        </div>
                        <div>
                            <h3 class="font-semibold text-slate-900 text-[15px]">{{ $provider['name'] }}</h3>
                            <div class="flex items-center gap-2 mt-1">
                                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                </svg>
                                <span class="text-xs text-slate-500">{{ $provider['area'] }}</span>
                                <span class="text-slate-300">·</span>
                                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-xs text-slate-500">{{ $provider['travel_time_min'] ?? '?' }} min away</span>
                            </div>
                            @if(!empty($provider['reasoning']))
                                <p class="text-xs text-slate-400 mt-1.5 italic">{{ $provider['reasoning'] }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <div class="text-xl font-bold text-slate-900">PKR {{ number_format($provider['price_min']) }}</div>
                        <div class="text-[11px] text-slate-400 mb-1">from</div>
                        <div class="text-xs font-semibold text-teal-600 bg-teal-50 px-2 py-0.5 rounded-lg">
                            {{ number_format(($provider['match_score'] ?? 0) * 100, 1) }}% match
                        </div>
                    </div>
                </div>

                <!-- Stats -->
                <div class="mt-4 grid grid-cols-4 gap-2">
                    @foreach([
                        ['Rating',      number_format($provider['rating_avg'] ?? 0, 1) . ' ★', 'text-amber-600', 'bg-amber-50'],
                        ['On time',     ($provider['on_time_score'] ?? 0) . '%',               'text-blue-600',  'bg-blue-50'],
                        ['Cancel rate', ($provider['cancel_rate'] ?? 0) . '%',                 'text-red-500',   'bg-red-50'],
                        ['Distance',    ($provider['distance_km'] ?? '?') . ' km',             'text-slate-600', 'bg-slate-50'],
                    ] as [$lbl, $val, $textCls, $bgCls])
                        <div class="{{ $bgCls }} rounded-xl p-2.5 text-center">
                            <div class="text-sm font-semibold {{ $textCls }}">{{ $val }}</div>
                            <div class="text-[11px] text-slate-400 mt-0.5">{{ $lbl }}</div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 flex justify-end">
                    <form method="POST" action="{{ route('user.quote') }}">
                        @csrf
                        <input type="hidden" name="provider_id" value="{{ $provider['id'] }}">
                        <input type="hidden" name="service_request_id" value="{{ $serviceRequest->id }}">
                        <button type="submit"
                                class="inline-flex items-center gap-2 {{ $i === 0 ? 'bg-teal-600 hover:bg-teal-500' : 'bg-slate-900 hover:bg-slate-800' }} text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-all shadow-sm">
                            Get Quote
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm px-6 py-16 text-center">
            <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>
                </svg>
            </div>
            <div class="text-sm font-medium text-slate-600 mb-1">No providers found</div>
            <div class="text-xs text-slate-400">Try a different service type or location.</div>
        </div>
    @endforelse
</div>
@endsection
