@extends('layouts.app')
@section('title', 'New Request')
@section('heading', 'New Service Request')
@section('subheading', 'Describe your need in Urdu, Roman Urdu, or English')

@section('content')
<div class="max-w-2xl">

    @if(session('clarification'))
        <div class="mb-5 flex items-start gap-3 p-4 bg-blue-50 border border-blue-200 rounded-2xl">
            <div class="w-7 h-7 bg-blue-500 rounded-lg flex items-center justify-center text-white text-[10px] font-bold flex-shrink-0 mt-0.5">AI</div>
            <div>
                <div class="text-sm font-semibold text-blue-900 mb-1">Clarification needed</div>
                <div class="text-sm text-blue-700">{{ session('clarification') }}</div>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 pt-6 pb-5 border-b border-slate-100">
            <h2 class="text-sm font-semibold text-slate-800">What do you need?</h2>
            <p class="text-xs text-slate-400 mt-0.5">AI will parse your request and find the best local providers</p>
        </div>

        <form method="POST" action="{{ route('user.request.store') }}" class="p-6 space-y-5">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-2">Describe your need</label>
                <textarea name="input" rows="3" required maxlength="500"
                          class="w-full px-3.5 py-3 text-sm text-slate-900 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all resize-none @error('input') border-red-300 @enderror"
                          placeholder="Mujhe kal subah Gulshan mein AC technician chahiye...">{{ old('input') }}</textarea>
                <div class="flex items-center justify-between mt-1.5">
                    <span class="text-[11px] text-slate-400">Urdu, Roman Urdu, or English accepted</span>
                </div>
            </div>

            <!-- Quick examples -->
            <div class="flex flex-wrap gap-2">
                @foreach([
                    'AC repair in Gulshan urgently',
                    'Mujhe plumber chahiye kal',
                    'Electrician needed in DHA',
                ] as $example)
                    <button type="button"
                            onclick="document.querySelector('textarea[name=input]').value = '{{ $example }}'"
                            class="text-xs px-3 py-1.5 bg-slate-100 hover:bg-teal-50 hover:text-teal-700 hover:border-teal-200 text-slate-600 border border-slate-200 rounded-lg transition-all">
                        {{ $example }}
                    </button>
                @endforeach
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-2">Latitude</label>
                    <input type="number" name="lat" step="any" value="{{ old('lat', '24.9260') }}" required
                           class="w-full px-3.5 py-2.5 text-sm text-slate-900 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all font-mono"
                           placeholder="24.9260">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wide mb-2">Longitude</label>
                    <input type="number" name="lng" step="any" value="{{ old('lng', '67.0930') }}" required
                           class="w-full px-3.5 py-2.5 text-sm text-slate-900 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all font-mono"
                           placeholder="67.0930">
                </div>
            </div>

            <div class="flex items-center gap-2 p-3 bg-amber-50 border border-amber-200 rounded-xl">
                <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p class="text-xs text-amber-800">Default coordinates: Gulshan-e-Iqbal, Karachi</p>
            </div>

            <div class="flex items-center gap-3 pt-1">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-500 active:bg-teal-700 text-white text-sm font-semibold px-6 py-2.5 rounded-xl transition-all shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Parse & Find Providers
                </button>
                <a href="{{ route('user.dashboard') }}" class="text-sm text-slate-400 hover:text-slate-600 transition-colors">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
