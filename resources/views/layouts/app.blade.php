<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ServiceHub') — ServiceHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="h-full bg-slate-50">

<div class="flex h-full">
    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 z-50 w-60 flex flex-col bg-slate-950 border-r border-slate-800/50">
        <!-- Brand -->
        <div class="flex items-center gap-3 px-5 h-16 border-b border-slate-800/60 flex-shrink-0">
            <div class="w-8 h-8 bg-gradient-to-br from-teal-400 to-teal-600 rounded-lg flex items-center justify-center shadow-sm">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <span class="text-white font-semibold tracking-tight">ServiceHub</span>
            @auth
                <span class="ml-auto text-[10px] font-medium px-1.5 py-0.5 rounded-md
                    {{ auth()->user()->role === 'admin' ? 'bg-rose-500/20 text-rose-400 ring-1 ring-rose-500/30' :
                       (auth()->user()->role === 'provider' ? 'bg-violet-500/20 text-violet-400 ring-1 ring-violet-500/30' : 'bg-teal-500/20 text-teal-400 ring-1 ring-teal-500/30') }}">
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
                @elseif(auth()->user()->role === 'provider')
                    <x-nav-item href="{{ route('provider.dashboard') }}" icon="grid" label="Dashboard" />
                    <x-nav-item href="{{ route('provider.bookings') }}" icon="calendar" label="My Bookings" />
                    <x-nav-item href="{{ route('provider.profile') }}" icon="user" label="My Profile" />
                @else
                    <x-nav-item href="{{ route('user.dashboard') }}" icon="grid" label="Dashboard" />
                    <x-nav-item href="{{ route('user.request') }}" icon="plus" label="New Request" />
                    <x-nav-item href="{{ route('user.bookings') }}" icon="calendar" label="My Bookings" />
                    <x-nav-item href="{{ route('user.disputes') }}" icon="alert" label="Disputes" />
                @endif

                <div class="pt-3 mt-3 border-t border-slate-800/60">
                    <x-nav-item href="/docs" icon="book" label="API Docs" external />
                </div>
            @endauth
        </nav>

        <!-- User footer -->
        @auth
        <div class="px-3 py-3 border-t border-slate-800/60 flex-shrink-0">
            <div class="flex items-center gap-3 px-2 py-2 rounded-lg">
                <div class="w-8 h-8 bg-gradient-to-br from-slate-600 to-slate-700 rounded-full flex items-center justify-center text-slate-300 text-xs font-semibold flex-shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium text-slate-200 truncate">{{ auth()->user()->name }}</div>
                    <div class="text-xs text-slate-500 truncate">{{ auth()->user()->email }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Sign out"
                            class="p-1.5 text-slate-500 hover:text-slate-300 hover:bg-slate-800 rounded-md transition-colors">
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
        <header class="sticky top-0 z-40 h-16 bg-white/80 backdrop-blur-sm border-b border-slate-200/80 flex items-center px-8 gap-4">
            <div class="flex-1 min-w-0">
                <h1 class="text-[15px] font-semibold text-slate-800 leading-tight">@yield('heading', 'Dashboard')</h1>
                @hasSection('subheading')
                    <p class="text-xs text-slate-400 mt-0.5">@yield('subheading')</p>
                @endif
            </div>
            <div class="flex items-center gap-2">
                @yield('header-actions')
            </div>
        </header>

        <!-- Alerts -->
        <div class="px-8 pt-5">
            @if(session('success'))
                <div class="mb-4 flex items-center gap-3 p-3.5 bg-teal-50 border border-teal-200 rounded-xl text-sm text-teal-800">
                    <div class="w-5 h-5 rounded-full bg-teal-500 flex items-center justify-center flex-shrink-0">
                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 flex items-center gap-3 p-3.5 bg-red-50 border border-red-200 rounded-xl text-sm text-red-800">
                    <div class="w-5 h-5 rounded-full bg-red-500 flex items-center justify-center flex-shrink-0">
                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="mb-4 p-3.5 bg-red-50 border border-red-200 rounded-xl text-sm text-red-800">
                    <ul class="space-y-1">
                        @foreach($errors->all() as $error)
                            <li class="flex items-center gap-2">
                                <span class="w-1 h-1 rounded-full bg-red-400 flex-shrink-0"></span>{{ $error }}
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
