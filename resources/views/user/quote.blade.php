@extends('layouts.app')
@section('title', 'Price Quote')
@section('heading', 'Your Price Quote')
@section('subheading', 'Transparent breakdown — no hidden charges')

@section('content')
<div class="max-w-xl space-y-4">

    <!-- Provider + total -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-5 bg-gradient-to-br from-slate-50 to-white border-b border-slate-100">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="text-[15px] font-semibold text-slate-900">{{ $provider->name }}</div>
                    <div class="flex items-center gap-2 mt-1">
                        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        </svg>
                        <span class="text-xs text-slate-500">{{ $provider->area }}</span>
                        <span class="text-slate-300">·</span>
                        <span class="text-xs text-slate-500">From PKR {{ number_format($provider->price_min) }}</span>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-3xl font-bold text-slate-900">PKR {{ number_format($quote->total) }}</div>
                    <div class="text-xs text-teal-600 font-medium mt-1">Provider gets PKR {{ number_format($quote->provider_net) }}</div>
                </div>
            </div>
        </div>

        <!-- Breakdown -->
        <div class="px-6 py-5 space-y-3">
            @foreach([
                ['Base rate',          $quote->base_rate,    false],
                ['Visit fee',          $quote->visit_fee,    false],
                ['Distance cost',      $quote->distance_cost,false],
                ['Urgency adjustment', $quote->urgency_adj,  false],
            ] as [$label, $val, $highlight])
                <div class="flex justify-between items-center text-sm">
                    <span class="text-slate-500">{{ $label }}</span>
                    <span class="font-medium text-slate-800">PKR {{ number_format($val) }}</span>
                </div>
            @endforeach

            @if($quote->surge_factor > 1)
                <div class="flex justify-between items-center text-sm">
                    <div class="flex items-center gap-2">
                        <span class="text-amber-600">Surge multiplier</span>
                        <span class="text-[10px] bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded font-medium">high demand</span>
                    </div>
                    <span class="font-medium text-amber-700">×{{ $quote->surge_factor }}</span>
                </div>
            @endif
            @if($quote->loyalty_discount > 0)
                <div class="flex justify-between items-center text-sm">
                    <div class="flex items-center gap-2">
                        <span class="text-teal-600">Loyalty discount</span>
                        <span class="text-[10px] bg-teal-100 text-teal-700 px-1.5 py-0.5 rounded font-medium">5% off</span>
                    </div>
                    <span class="font-medium text-teal-700">−PKR {{ number_format($quote->loyalty_discount) }}</span>
                </div>
            @endif

            <div class="pt-3 border-t border-slate-100 flex justify-between items-center">
                <span class="text-sm font-semibold text-slate-800">Total</span>
                <span class="text-lg font-bold text-slate-900">PKR {{ number_format($quote->total) }}</span>
            </div>
        </div>
    </div>

    @if($budgetQuote)
        <div class="flex items-center gap-3 p-4 bg-blue-50 border border-blue-200 rounded-2xl">
            <svg class="w-5 h-5 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <div class="text-xs font-semibold text-blue-800">Budget alternative available</div>
                <div class="text-sm text-blue-700 mt-0.5">PKR {{ number_format($budgetQuote->total) }} with a lower-tier provider</div>
            </div>
        </div>
    @endif

    <!-- Booking form -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <form method="POST" action="{{ route('user.bookings.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="provider_id" value="{{ $provider->id }}">
            <input type="hidden" name="service_request_id" value="{{ $serviceRequest->id }}">
            <input type="hidden" name="pricing_quote_id" value="{{ $quote->id }}">

            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-2">Choose appointment slot</label>
                <input type="datetime-local" name="slot_datetime" required
                       min="{{ now()->addHour()->format('Y-m-d\TH:i') }}"
                       class="w-full px-3.5 py-2.5 text-sm text-slate-900 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all">
                <p class="text-xs text-slate-400 mt-1.5">Available Mon–Fri · 8 AM – 8 PM</p>
            </div>

            <div class="flex items-center gap-3 pt-1">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-500 active:bg-teal-700 text-white text-sm font-semibold px-6 py-2.5 rounded-xl transition-all shadow-sm">
                    Confirm Booking
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </button>
                <a href="{{ route('user.dashboard') }}" class="text-sm text-slate-400 hover:text-slate-600 transition-colors">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
