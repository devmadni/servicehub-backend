@extends('layouts.app')
@section('title', 'My Bookings')
@section('heading', 'My Bookings')

@section('header-actions')
    <a href="{{ route('user.request') }}"
       class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-500 text-white text-sm font-medium px-4 py-2 rounded-xl transition-all shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        New Request
    </a>
@endsection

@section('content')
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
    @if($bookings->isEmpty())
        <div class="px-6 py-16 text-center">
            <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="text-sm font-medium text-slate-600 mb-1">No bookings yet</div>
            <div class="text-xs text-slate-400 mb-4">Your booking history will appear here.</div>
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
                   class="flex items-center gap-4 px-6 py-4 hover:bg-slate-50 transition-colors group">
                    <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center text-[11px] font-mono font-semibold text-slate-500 flex-shrink-0">
                        {{ substr($booking->booking_ref, -4) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-slate-800">{{ $booking->serviceRequest?->service_type ?? 'Service' }}</div>
                        <div class="text-xs text-slate-400 mt-0.5">
                            {{ $booking->provider?->name }}
                            <span class="text-slate-300 mx-1">·</span>
                            {{ $booking->slot_datetime?->format('M j, Y · h:i A') }}
                        </div>
                    </div>
                    <div class="flex items-center gap-3 flex-shrink-0">
                        @if($booking->pricingQuote)
                            <span class="text-sm font-semibold text-slate-700">PKR {{ number_format($booking->pricingQuote->total) }}</span>
                        @endif
                        <x-status-badge :status="$booking->status" />
                        <svg class="w-4 h-4 text-slate-300 group-hover:text-slate-500 transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </a>
            @endforeach
        </div>
        @if($bookings->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $bookings->links() }}
            </div>
        @endif
    @endif
</div>
@endsection
