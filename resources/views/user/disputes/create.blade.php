@extends('layouts.app')
@section('title', 'Open Dispute')
@section('heading', 'Open a Dispute')

@section('content')
<div class="max-w-xl">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 pt-5 pb-4 border-b border-slate-100">
            <h2 class="text-sm font-semibold text-slate-800">Dispute details</h2>
            <p class="text-xs text-slate-400 mt-0.5">Our AI generates an instant Stage 1 resolution — you can accept or escalate.</p>
        </div>

        <form method="POST" action="{{ route('user.disputes.store') }}" class="p-6 space-y-5">
            @csrf
            <input type="hidden" name="booking_id" value="{{ request('booking_id') }}">

            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-2">Issue type</label>
                <select name="trigger_type" required
                        class="w-full px-3.5 py-2.5 text-sm text-slate-900 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all">
                    <option value="">Select issue type…</option>
                    @foreach([
                        'no_show' => 'Provider No-Show',
                        'late'    => 'Provider Late',
                        'quality' => 'Poor Quality',
                        'price'   => 'Price Dispute',
                        'damage'  => 'Property Damage',
                        'refund'  => 'Refund Request',
                    ] as $val => $label)
                        <option value="{{ $val }}" {{ old('trigger_type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-2">Description</label>
                <textarea name="description" rows="4" required minlength="10"
                          class="w-full px-3.5 py-3 text-sm text-slate-900 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all resize-none"
                          placeholder="Describe the issue in detail…">{{ old('description') }}</textarea>
            </div>

            <div class="flex items-start gap-3 p-3.5 bg-blue-50 border border-blue-200 rounded-xl">
                <svg class="w-4 h-4 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-xs text-blue-800">
                    The AI generates an automated resolution offer (e.g. refund or free re-service). You can accept, or escalate up to Stage 3 for human review.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-500 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-all shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    Submit Dispute
                </button>
                <a href="{{ route('user.disputes') }}" class="text-sm text-slate-400 hover:text-slate-600 transition-colors">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
