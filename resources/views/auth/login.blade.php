@extends('layouts.guest')
@section('title', 'Sign in')

@section('content')
<div>
    <div class="mb-8">
        <h1 class="text-2xl font-bold" style="color: #0B1220;">Welcome back</h1>
        <p class="text-sm mt-1" style="color: #6B6B70;">Sign in to your ServiceHub account</p>
    </div>

    @if($errors->any())
        <div class="mb-5 p-3.5 rounded-xl text-sm" style="background: #FEF2F2; border: 1px solid rgba(217,79,61,0.25); color: #B91C1C;">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color: #6B6B70;">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="w-full px-3.5 py-2.5 text-sm rounded-xl transition-all @error('email') border-red-300 bg-red-50 @enderror"
                   style="color: #1C1C1E; border: 1px solid #E0E0E0; background: #F7F6F2; outline: none;"
                   onfocus="this.style.borderColor='#1A6B5A'; this.style.boxShadow='0 0 0 3px rgba(26,107,90,0.12)'; this.style.background='#fff';"
                   onblur="this.style.borderColor='#E0E0E0'; this.style.boxShadow='none'; this.style.background='#F7F6F2';"
                   placeholder="you@example.com">
        </div>

        <div>
            <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color: #6B6B70;">Password</label>
            <input type="password" name="password" required
                   class="w-full px-3.5 py-2.5 text-sm rounded-xl transition-all"
                   style="color: #1C1C1E; border: 1px solid #E0E0E0; background: #F7F6F2; outline: none;"
                   onfocus="this.style.borderColor='#1A6B5A'; this.style.boxShadow='0 0 0 3px rgba(26,107,90,0.12)'; this.style.background='#fff';"
                   onblur="this.style.borderColor='#E0E0E0'; this.style.boxShadow='none'; this.style.background='#F7F6F2';"
                   placeholder="••••••••">
        </div>

        <button type="submit"
                class="w-full text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm mt-2 transition-opacity hover:opacity-90 active:opacity-80"
                style="background: linear-gradient(135deg, #0D4A3E 0%, #1A6B5A 50%, #14B8A6 100%);">
            Sign in
        </button>
    </form>

    <p class="mt-6 text-center text-sm" style="color: #6B6B70;">
        Don't have an account?
        <a href="{{ route('register') }}" class="font-semibold hover:opacity-80 transition-opacity" style="color: #1A6B5A;">Register</a>
    </p>

    <!-- Demo accounts -->
    <div class="mt-8 p-4 rounded-xl" style="background: #F7F6F2; border: 1px solid #E8EFED;">
        <p class="text-xs font-semibold uppercase tracking-wide mb-3" style="color: #9AA3B2;">Demo accounts</p>
        <div class="space-y-2">
            <!-- Admin -->
            <button type="button" onclick="fillCreds('admin@servicehub.test')"
                    class="w-full flex items-center justify-between px-3 py-2 bg-white rounded-lg cursor-pointer text-left transition-all"
                    style="border: 1px solid #E0E0E0;"
                    onmouseover="this.style.borderColor='#F5C842'; this.style.background='#FFFBEB';"
                    onmouseout="this.style.borderColor='#E0E0E0'; this.style.background='white';">
                <div>
                    <div class="text-xs font-medium" style="color: #1C1C1E;">admin@servicehub.test</div>
                    <div class="text-[11px] mt-0.5" style="color: #9AA3B2;">Full platform control</div>
                </div>
                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full" style="background: #FEF9C3; color: #92400E;">Admin</span>
            </button>
            <!-- User -->
            <button type="button" onclick="fillCreds('test@example.com')"
                    class="w-full flex items-center justify-between px-3 py-2 bg-white rounded-lg cursor-pointer text-left transition-all"
                    style="border: 1px solid #E0E0E0;"
                    onmouseover="this.style.borderColor='#1A6B5A'; this.style.background='#D5F5F0';"
                    onmouseout="this.style.borderColor='#E0E0E0'; this.style.background='white';">
                <div>
                    <div class="text-xs font-medium" style="color: #1C1C1E;">test@example.com</div>
                    <div class="text-[11px] mt-0.5" style="color: #9AA3B2;">Customer account</div>
                </div>
                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full" style="background: #D5F5F0; color: #0D4A3E;">User</span>
            </button>
        </div>
        <p class="text-[11px] mt-2 text-center" style="color: #9AA3B2;">Password: <code class="font-mono">password</code></p>
    </div>
</div>

<script>
function fillCreds(email) {
    document.querySelector('input[name=email]').value = email;
    document.querySelector('input[name=password]').value = 'password';
}
</script>
@endsection
