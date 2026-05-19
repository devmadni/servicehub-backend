@extends('layouts.app')
@section('title', 'Booking ' . $booking->booking_ref)
@section('heading', $booking->booking_ref)
@section('subheading', 'Booking details')

@section('header-actions')
    @if($booking->status === 'pending')
        <form method="POST" action="{{ route('user.bookings.confirm', $booking) }}">
            @csrf @method('PUT')
            <button class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-500 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-all shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Confirm Booking
            </button>
        </form>
    @endif
    @if(!in_array($booking->status, ['completed', 'disputed', 'cancelled']))
        <form method="POST" action="{{ route('user.bookings.cancel', $booking) }}">
            @csrf @method('DELETE')
            <button onclick="return confirm('Cancel this booking?')"
                    class="inline-flex items-center gap-2 border border-red-200 text-red-600 hover:bg-red-50 text-sm font-medium px-4 py-2 rounded-xl transition-all">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Cancel
            </button>
        </form>
    @endif
@endsection

@section('content')
<div class="max-w-2xl space-y-4">

    <!-- Status + slot -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 flex items-center justify-between gap-4">
        <div>
            <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-2">Status</div>
            <x-status-badge :status="$booking->status" size="lg" />
        </div>
        <div class="h-8 w-px bg-slate-100"></div>
        <div class="flex-1">
            <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-1">Appointment</div>
            <div class="text-sm font-semibold text-slate-800">{{ $booking->slot_datetime?->format('M j, Y') }}</div>
            <div class="text-xs text-slate-500">{{ $booking->slot_datetime?->format('h:i A') }}</div>
        </div>
        <div class="text-right">
            <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-1">Ref</div>
            <div class="text-sm font-mono font-semibold text-slate-700">{{ $booking->booking_ref }}</div>
        </div>
    </div>

    <!-- Service + Provider + Price -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm divide-y divide-slate-100">
        <div class="px-5 py-4 flex items-center gap-4">
            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div>
                <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Service</div>
                <div class="text-sm font-semibold text-slate-800 mt-0.5">{{ $booking->serviceRequest?->service_type }}</div>
                <div class="text-xs text-slate-400">Complexity: {{ ucfirst($booking->complexity) }}</div>
            </div>
        </div>
        <div class="px-5 py-4 flex items-center gap-4">
            <div class="w-8 h-8 bg-teal-100 rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div>
                <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Provider</div>
                <div class="text-sm font-semibold text-slate-800 mt-0.5">{{ $booking->provider?->name }}</div>
                <div class="text-xs text-slate-400">{{ $booking->provider?->area }} · ⭐ {{ $booking->provider?->rating_avg }}</div>
            </div>
        </div>
        @if($booking->pricingQuote)
            <div class="px-5 py-4 flex items-center gap-4">
                <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Payment</div>
                    <div class="text-xl font-bold text-slate-900 mt-0.5">PKR {{ number_format($booking->pricingQuote->total) }}</div>
                </div>
                <div class="text-right text-xs text-slate-400">
                    Provider gets<br>PKR {{ number_format($booking->pricingQuote->provider_net) }}
                </div>
            </div>
        @endif
    </div>

    <!-- Feedback (completed) -->
    @if($booking->status === 'completed')
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-slate-800 mb-4">Rate your experience</h3>
            <form method="POST" action="{{ route('user.bookings.feedback', $booking) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs text-slate-500 mb-2">Rating</label>
                    <div class="flex gap-1" id="star-container">
                        @for($r = 1; $r <= 5; $r++)
                            <label class="cursor-pointer">
                                <input type="radio" name="rating" value="{{ $r }}" class="sr-only" required>
                                <svg class="w-8 h-8 text-slate-200 hover:text-amber-400 transition-colors star-icon" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </label>
                        @endfor
                    </div>
                </div>
                <textarea name="review" rows="3" required
                          class="w-full px-3.5 py-2.5 text-sm text-slate-900 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all resize-none"
                          placeholder="How was your experience?"></textarea>
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-500 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-all shadow-sm">
                    Submit Feedback
                </button>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-slate-800 mb-2">Have an issue?</h3>
            <p class="text-xs text-slate-400 mb-3">Open a dispute and our AI will mediate immediately.</p>
            <a href="{{ route('user.disputes.create', ['booking_id' => $booking->id]) }}"
               class="inline-flex items-center gap-2 text-sm font-medium text-red-600 hover:text-red-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                Open a dispute
            </a>
        </div>
    @endif

    <!-- AI trace -->
    @if($booking->agent_run_id)
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 bg-violet-100 rounded-md flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 text-violet-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-slate-800">AI Agent Trace</h3>
                </div>
                <span class="text-[11px] font-mono text-slate-400">{{ substr($booking->agent_run_id, 0, 8) }}…</span>
            </div>
            <a href="/api/v1/agent-trace/{{ $booking->agent_run_id }}/export" target="_blank"
               class="mt-3 inline-flex items-center gap-1.5 text-xs font-medium text-violet-600 hover:text-violet-700">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export trace JSON
            </a>
        </div>
    @endif
</div>

<script>
document.querySelectorAll('#star-container input[type=radio]').forEach(function(radio, idx) {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.star-icon').forEach(function(star, i) {
            star.classList.toggle('text-amber-400', i <= idx);
            star.classList.toggle('text-slate-200', i > idx);
        });
    });
});
</script>
@endsection
