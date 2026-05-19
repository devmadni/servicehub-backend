@extends('layouts.guest')
@section('title', 'Create account')

@section('content')
<div>
    <div class="mb-8">
        <h1 class="text-2xl font-bold" style="color: #0B1220;">Create account</h1>
        <p class="text-sm mt-1" style="color: #6B6B70;">Join ServiceHub as a customer</p>
    </div>

    @if($errors->any())
        <div class="mb-5 p-3.5 rounded-xl text-sm space-y-1" style="background: #FEF2F2; border: 1px solid rgba(217,79,61,0.25); color: #B91C1C;">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color: #6B6B70;">Full name</label>
            <input type="text" name="name" value="{{ old('name') }}" required autofocus
                   class="w-full px-3.5 py-2.5 text-sm rounded-xl transition-all @error('name') border-red-300 @enderror"
                   style="color: #1C1C1E; border: 1px solid #E0E0E0; background: #F7F6F2; outline: none;"
                   onfocus="this.style.borderColor='#1A6B5A'; this.style.boxShadow='0 0 0 3px rgba(26,107,90,0.12)'; this.style.background='#fff';"
                   onblur="this.style.borderColor='#E0E0E0'; this.style.boxShadow='none'; this.style.background='#F7F6F2';"
                   placeholder="Ahmed Khan">
        </div>

        <div>
            <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color: #6B6B70;">
                Phone <span class="normal-case font-normal" style="color: #9AA3B2;">(optional)</span>
            </label>
            <input type="tel" name="phone" value="{{ old('phone') }}"
                   class="w-full px-3.5 py-2.5 text-sm rounded-xl transition-all"
                   style="color: #1C1C1E; border: 1px solid #E0E0E0; background: #F7F6F2; outline: none;"
                   onfocus="this.style.borderColor='#1A6B5A'; this.style.boxShadow='0 0 0 3px rgba(26,107,90,0.12)'; this.style.background='#fff';"
                   onblur="this.style.borderColor='#E0E0E0'; this.style.boxShadow='none'; this.style.background='#F7F6F2';"
                   placeholder="+92 300 0000000">
        </div>

        <div>
            <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color: #6B6B70;">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required
                   class="w-full px-3.5 py-2.5 text-sm rounded-xl transition-all @error('email') border-red-300 @enderror"
                   style="color: #1C1C1E; border: 1px solid #E0E0E0; background: #F7F6F2; outline: none;"
                   onfocus="this.style.borderColor='#1A6B5A'; this.style.boxShadow='0 0 0 3px rgba(26,107,90,0.12)'; this.style.background='#fff';"
                   onblur="this.style.borderColor='#E0E0E0'; this.style.boxShadow='none'; this.style.background='#F7F6F2';"
                   placeholder="you@example.com">
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color: #6B6B70;">Password</label>
                <input type="password" name="password" required
                       class="w-full px-3.5 py-2.5 text-sm rounded-xl transition-all"
                       style="color: #1C1C1E; border: 1px solid #E0E0E0; background: #F7F6F2; outline: none;"
                       onfocus="this.style.borderColor='#1A6B5A'; this.style.boxShadow='0 0 0 3px rgba(26,107,90,0.12)'; this.style.background='#fff';"
                       onblur="this.style.borderColor='#E0E0E0'; this.style.boxShadow='none'; this.style.background='#F7F6F2';"
                       placeholder="Min 8 chars">
            </div>
            <div>
                <label class="block text-xs font-semibold mb-1.5 uppercase tracking-wide" style="color: #6B6B70;">Confirm</label>
                <input type="password" name="password_confirmation" required
                       class="w-full px-3.5 py-2.5 text-sm rounded-xl transition-all"
                       style="color: #1C1C1E; border: 1px solid #E0E0E0; background: #F7F6F2; outline: none;"
                       onfocus="this.style.borderColor='#1A6B5A'; this.style.boxShadow='0 0 0 3px rgba(26,107,90,0.12)'; this.style.background='#fff';"
                       onblur="this.style.borderColor='#E0E0E0'; this.style.boxShadow='none'; this.style.background='#F7F6F2';"
                       placeholder="Repeat">
            </div>
        </div>

        <button type="submit"
                class="w-full text-white text-sm font-semibold py-2.5 rounded-xl shadow-sm mt-2 transition-opacity hover:opacity-90 active:opacity-80"
                style="background: linear-gradient(135deg, #0D4A3E 0%, #1A6B5A 50%, #14B8A6 100%);">
            Create account
        </button>
    </form>

    <p class="mt-6 text-center text-sm" style="color: #6B6B70;">
        Already have an account?
        <a href="{{ route('login') }}" class="font-semibold hover:opacity-80 transition-opacity" style="color: #1A6B5A;">Sign in</a>
    </p>
</div>
@endsection
