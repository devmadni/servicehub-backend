@extends('layouts.app')
@section('title', 'All Bookings')
@section('heading', 'All Bookings')
@section('subheading', $bookings->total() . ' total bookings across all users')

@section('content')
<div class="flex gap-1.5 mb-5 flex-wrap">
    @foreach(['all' => 'All', 'pending' => 'Pending', 'confirmed' => 'Confirmed', 'completed' => 'Completed', 'disputed' => 'Disputed', 'cancelled' => 'Cancelled'] as $val => $label)
        <a href="{{ route('admin.bookings', ['status' => $val]) }}"
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
        <div class="px-6 py-16 text-center text-sm text-slate-400">No bookings found.</div>
    @else
        <div class="divide-y divide-slate-100">
            @foreach($bookings as $booking)
                <div class="px-6 py-4 flex items-center gap-4">
                    <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center text-[11px] font-mono font-semibold text-slate-500 flex-shrink-0">
                        {{ substr($booking->booking_ref, -4) }}
                    </div>
                    <div class="flex-1 grid grid-cols-3 gap-4 min-w-0">
                        <div class="min-w-0">
                            <div class="text-sm font-medium text-slate-800 truncate">{{ $booking->serviceRequest?->service_type }}</div>
                            <div class="text-xs text-slate-400 mt-0.5">{{ $booking->user?->name }}</div>
                        </div>
                        <div class="min-w-0">
                            <div class="text-sm text-slate-700 truncate">{{ $booking->provider?->name }}</div>
                            <div class="text-xs text-slate-400 mt-0.5">{{ $booking->slot_datetime?->format('M j, h:i A') }}</div>
                        </div>
                        <div>
                            @if($booking->pricingQuote)
                                <div class="text-sm font-semibold text-slate-800">PKR {{ number_format($booking->pricingQuote->total) }}</div>
                            @endif
                            <div class="text-xs text-slate-400 mt-0.5">{{ $booking->created_at?->format('M j') }}</div>
                        </div>
                    </div>
                    <div class="flex-shrink-0">
                        <x-status-badge :status="$booking->status" />
                    </div>
                </div>
            @endforeach
        </div>
        @if($bookings->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">{{ $bookings->links() }}</div>
        @endif
    @endif
</div>
@endsection
