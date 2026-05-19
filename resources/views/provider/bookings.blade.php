@extends('layouts.app')
@section('title', 'My Bookings')
@section('heading', 'My Bookings')

@section('content')
<!-- Filter tabs -->
<div class="flex gap-1.5 mb-5 flex-wrap">
    @foreach(['all' => 'All', 'confirmed' => 'Confirmed', 'en_route' => 'En Route', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $val => $label)
        <a href="{{ route('provider.bookings', ['status' => $val]) }}"
           class="px-3.5 py-1.5 rounded-lg text-xs font-semibold transition-all
                  {{ (request('status', 'all') === $val)
                      ? 'bg-slate-900 text-white shadow-sm'
                      : 'bg-white text-slate-500 border border-slate-200 hover:border-slate-300 hover:text-slate-700' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
    @if($bookings->isEmpty())
        <div class="px-6 py-16 text-center">
            <div class="w-11 h-11 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="text-sm text-slate-500">No bookings found.</div>
        </div>
    @else
        <div class="divide-y divide-slate-100">
            @foreach($bookings as $booking)
                <div class="px-6 py-4 flex items-center gap-4">
                    <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center text-[11px] font-mono font-semibold text-slate-500 flex-shrink-0">
                        {{ substr($booking->booking_ref, -4) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-slate-800">{{ $booking->serviceRequest?->service_type }}</div>
                        <div class="text-xs text-slate-400 mt-0.5">
                            {{ $booking->user?->name }}
                            <span class="text-slate-300 mx-1">·</span>
                            {{ $booking->slot_datetime?->format('M j, Y · h:i A') }}
                        </div>
                    </div>
                    <x-status-badge :status="$booking->status" />
                    @if(in_array($booking->status, ['confirmed', 'en_route']))
                        <form method="POST" action="{{ route('provider.bookings.status', $booking) }}">
                            @csrf @method('PUT')
                            <input type="hidden" name="status" value="{{ $booking->status === 'confirmed' ? 'en_route' : 'completed' }}">
                            <button class="text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors
                                {{ $booking->status === 'confirmed'
                                    ? 'bg-blue-100 text-blue-700 hover:bg-blue-200'
                                    : 'bg-teal-100 text-teal-700 hover:bg-teal-200' }}">
                                {{ $booking->status === 'confirmed' ? 'En Route' : 'Complete' }}
                            </button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>
        @if($bookings->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">{{ $bookings->links() }}</div>
        @endif
    @endif
</div>
@endsection
