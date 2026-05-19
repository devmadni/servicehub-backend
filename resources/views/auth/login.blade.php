@extends('layouts.guest')
@section('title', 'Sign in')

@section('content')
<div>
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-900">Welcome back</h1>
        <p class="text-sm text-slate-500 mt-1">Sign in to your ServiceHub account</p>
    </div>

    @if($errors->any())
        <div class="mb-5 p-3.5 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wide">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="w-full px-3.5 py-2.5 text-sm text-slate-900 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all @error('email') border-red-300 bg-red-50 @enderror"
                   placeholder="you@example.com">
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase tracking-wide">Password</label>
            <input type="password" name="password" required
                   class="w-full px-3.5 py-2.5 text-sm text-slate-900 border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all"
                   placeholder="••••••••">
        </div>

        <button type="submit"
                class="w-full bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white text-sm font-semibold py-2.5 rounded-xl transition-all shadow-sm mt-2">
            Sign in
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500">
        Don't have an account?
        <a href="{{ route('register') }}" class="text-teal-600 font-semibold hover:text-teal-700">Register</a>
    </p>

    <!-- Demo accounts -->
    <div class="mt-8 p-4 bg-slate-50 rounded-xl border border-slate-200">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3">Demo accounts</p>
        <div class="space-y-2">
            <button type="button" onclick="fillCreds('test@example.com')"
                    class="w-full flex items-center justify-between px-3 py-2 bg-white border border-slate-200 rounded-lg hover:border-teal-300 hover:bg-teal-50 transition-all cursor-pointer text-left">
                <div>
                    <div class="text-xs font-medium text-slate-700">test@example.com</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Customer account</div>
                </div>
                <span class="text-[10px] font-medium px-2 py-0.5 bg-teal-100 text-teal-700 rounded-full">User</span>
            </button>
            <button type="button" onclick="fillCreds('ali-ac-services@provider.test')"
                    class="w-full flex items-center justify-between px-3 py-2 bg-white border border-slate-200 rounded-lg hover:border-violet-300 hover:bg-violet-50 transition-all cursor-pointer text-left">
                <div>
                    <div class="text-xs font-medium text-slate-700">ali-ac-services@provider.test</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Ali AC Services · ⭐ 4.8</div>
                </div>
                <span class="text-[10px] font-medium px-2 py-0.5 bg-violet-100 text-violet-700 rounded-full">Provider</span>
            </button>
        </div>
        <p class="text-[11px] text-slate-400 mt-2 text-center">Password: <code class="font-mono">password</code></p>
    </div>
</div>

<script>
function fillCreds(email) {
    document.querySelector('input[name=email]').value = email;
    document.querySelector('input[name=password]').value = 'password';
}
</script>
@endsection
