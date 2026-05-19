@extends('layouts.guest')
@section('title', 'Create account')

@section('content')
<div>
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-900">Create account</h1>
        <p class="text-sm text-slate-500 mt-1">Join ServiceHub as a customer</p>
    </div>

    @if($errors->any())
        <div class="mb-5 p-3.5 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 space-y-1">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wide">Full name</label>
            <input type="text" name="name" value="{{ old('name') }}" required autofocus
                   class="w-full px-3.5 py-2.5 text-sm text-slate-900 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all @error('name') border-red-300 @enderror"
                   placeholder="Ahmed Khan">
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wide">
                Phone <span class="normal-case font-normal text-slate-400">(optional)</span>
            </label>
            <input type="tel" name="phone" value="{{ old('phone') }}"
                   class="w-full px-3.5 py-2.5 text-sm text-slate-900 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all"
                   placeholder="+92 300 0000000">
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wide">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required
                   class="w-full px-3.5 py-2.5 text-sm text-slate-900 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all @error('email') border-red-300 @enderror"
                   placeholder="you@example.com">
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wide">Password</label>
                <input type="password" name="password" required
                       class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all"
                       placeholder="Min 8 chars">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wide">Confirm</label>
                <input type="password" name="password_confirmation" required
                       class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all"
                       placeholder="Repeat">
            </div>
        </div>

        <button type="submit"
                class="w-full bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white text-sm font-semibold py-2.5 rounded-xl transition-all shadow-sm mt-2">
            Create account
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500">
        Already have an account?
        <a href="{{ route('login') }}" class="text-teal-600 font-semibold hover:text-teal-700">Sign in</a>
    </p>
</div>
@endsection
