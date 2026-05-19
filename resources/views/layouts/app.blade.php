<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ServiceHub') — ServiceHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .brand-gradient { background: linear-gradient(135deg, #0D4A3E 0%, #1A6B5A 50%, #14B8A6 100%); }
    </style>
</head>
<body class="h-full" style="background: #F7F6F2;">

<div class="flex h-full">
    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 z-50 w-60 flex flex-col" style="background: #0D4A3E; border-right: 1px solid rgba(6,78,71,0.8);">
        <!-- Brand -->
        <div class="flex items-center gap-3 px-5 h-16 flex-shrink-0" style="border-bottom: 1px solid rgba(255,255,255,0.08);">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center shadow-sm" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2);">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <span class="text-white font-bold tracking-tight">ServiceHub</span>
            @auth
                <span class="ml-auto text-[10px] font-semibold px-1.5 py-0.5 rounded-md"
                    style="{{ auth()->user()->role === 'admin'
                        ? 'background: rgba(245,200,66,0.2); color: #F5C842; outline: 1px solid rgba(245,200,66,0.35);'
                        : 'background: rgba(45,212,191,0.15); color: #2DD4BF; outline: 1px solid rgba(45,212,191,0.3);' }}">
                    {{ ucfirst(auth()->user()->role) }}
                </span>
            @endauth
        </div>

        <!-- Nav -->
        <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
            @auth
                @if(auth()->user()->role === 'admin')
                    <x-nav-item href="{{ route('admin.dashboard') }}" icon="grid" label="Dashboard" />
                    <x-nav-item href="{{ route('admin.providers') }}" icon="users" label="Providers" />
                    <x-nav-item href="{{ route('admin.bookings') }}" icon="calendar" label="All Bookings" />
                    <x-nav-item href="{{ route('admin.disputes') }}" icon="alert" label="Disputes" />
                @else
                    <x-nav-item href="{{ route('user.dashboard') }}" icon="grid" label="Dashboard" />
                    <x-nav-item href="{{ route('user.request') }}" icon="plus" label="New Request" />
                    <x-nav-item href="{{ route('user.bookings') }}" icon="calendar" label="My Bookings" />
                    <x-nav-item href="{{ route('user.disputes') }}" icon="alert" label="Disputes" />
                @endif

                <div class="pt-3 mt-3" style="border-top: 1px solid rgba(255,255,255,0.08);">
                    <x-nav-item href="/docs" icon="book" label="API Docs" external />
                </div>
            @endauth
        </nav>

        <!-- User footer -->
        @auth
        <div class="px-3 py-3 flex-shrink-0" style="border-top: 1px solid rgba(255,255,255,0.08);">
            <div class="flex items-center gap-3 px-2 py-2 rounded-lg">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0"
                     style="background: linear-gradient(135deg, #1A6B5A, #14B8A6); color: white;">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-semibold truncate" style="color: #D5F5F0;">{{ auth()->user()->name }}</div>
                    <div class="text-xs truncate" style="color: rgba(213,245,240,0.45);">{{ auth()->user()->email }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Sign out"
                            class="p-1.5 rounded-md transition-colors"
                            style="color: rgba(213,245,240,0.4);"
                            onmouseover="this.style.background='rgba(255,255,255,0.1)'; this.style.color='#D5F5F0';"
                            onmouseout="this.style.background='transparent'; this.style.color='rgba(213,245,240,0.4)';">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
        @endauth
    </aside>

    <!-- Main -->
    <div class="flex-1 flex flex-col min-h-full pl-60">
        <!-- Topbar -->
        <header class="sticky top-0 z-40 h-16 bg-white/90 backdrop-blur-sm flex items-center px-8 gap-4" style="border-bottom: 1px solid #E8EFED;">
            <div class="flex-1 min-w-0">
                <h1 class="text-[15px] font-semibold leading-tight" style="color: #0B1220;">@yield('heading', 'Dashboard')</h1>
                @hasSection('subheading')
                    <p class="text-xs mt-0.5" style="color: #9AA3B2;">@yield('subheading')</p>
                @endif
            </div>
            <div class="flex items-center gap-2">
                @yield('header-actions')
            </div>
        </header>

        <!-- Alerts -->
        <div class="px-8 pt-5">
            @if(session('success'))
                <div class="mb-4 flex items-center gap-3 p-3.5 rounded-xl text-sm" style="background: #D1FAE5; border: 1px solid rgba(46,158,107,0.3); color: #047857;">
                    <div class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0" style="background: #2E9E6B;">
                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 flex items-center gap-3 p-3.5 rounded-xl text-sm" style="background: #FEF2F2; border: 1px solid rgba(217,79,61,0.3); color: #B91C1C;">
                    <div class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0" style="background: #D94F3D;">
                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="mb-4 p-3.5 rounded-xl text-sm" style="background: #FEF2F2; border: 1px solid rgba(217,79,61,0.3); color: #B91C1C;">
                    <ul class="space-y-1">
                        @foreach($errors->all() as $error)
                            <li class="flex items-center gap-2">
                                <span class="w-1 h-1 rounded-full flex-shrink-0" style="background: #D94F3D;"></span>{{ $error }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <!-- Content -->
        <main class="flex-1 px-8 py-5 pb-16">
            @yield('content')
        </main>
    </div>
</div>

</body>
</html>
