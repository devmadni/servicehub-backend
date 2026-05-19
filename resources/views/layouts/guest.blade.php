<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ServiceHub') — ServiceHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="h-full bg-slate-50">
<div class="min-h-full flex">

    <!-- Left panel -->
    <div class="hidden lg:flex lg:w-[45%] bg-slate-950 flex-col relative overflow-hidden">
        <!-- Subtle gradient blobs -->
        <div class="absolute top-0 left-0 w-96 h-96 bg-teal-500/10 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute bottom-0 right-0 w-80 h-80 bg-violet-500/10 rounded-full blur-3xl translate-x-1/2 translate-y-1/2"></div>

        <div class="relative flex flex-col justify-between h-full p-12">
            <!-- Logo -->
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-gradient-to-br from-teal-400 to-teal-600 rounded-xl flex items-center justify-center shadow-lg">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <span class="text-white font-semibold text-lg tracking-tight">ServiceHub</span>
            </div>

            <!-- Hero text -->
            <div>
                <h2 class="text-white text-3xl font-light leading-snug mb-4">
                    AI-powered service<br>
                    <span class="text-teal-400 font-semibold">booking for Karachi</span>
                </h2>
                <p class="text-slate-400 text-sm leading-relaxed mb-10 max-w-xs">
                    Describe your need in Urdu, Roman Urdu, or English — our 6-agent AI pipeline handles the rest.
                </p>

                <!-- Stats -->
                <div class="grid grid-cols-3 gap-3">
                    <div class="bg-white/5 border border-white/10 rounded-xl p-4">
                        <div class="text-teal-400 text-2xl font-bold">14+</div>
                        <div class="text-slate-500 text-xs mt-1">Verified Providers</div>
                    </div>
                    <div class="bg-white/5 border border-white/10 rounded-xl p-4">
                        <div class="text-violet-400 text-2xl font-bold">6</div>
                        <div class="text-slate-500 text-xs mt-1">AI Agents</div>
                    </div>
                    <div class="bg-white/5 border border-white/10 rounded-xl p-4">
                        <div class="text-amber-400 text-2xl font-bold">31</div>
                        <div class="text-slate-500 text-xs mt-1">API Endpoints</div>
                    </div>
                </div>
            </div>

            <p class="text-slate-600 text-xs">Hackathon Demo · Karachi, Pakistan</p>
        </div>
    </div>

    <!-- Right panel -->
    <div class="flex-1 flex items-center justify-center p-8 bg-white">
        <div class="w-full max-w-sm">
            <!-- Mobile logo -->
            <div class="flex items-center gap-2 mb-8 lg:hidden">
                <div class="w-8 h-8 bg-gradient-to-br from-teal-400 to-teal-600 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <span class="font-semibold text-slate-800">ServiceHub</span>
            </div>
            @yield('content')
        </div>
    </div>
</div>
</body>
</html>
