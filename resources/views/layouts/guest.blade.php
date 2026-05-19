<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ServiceHub') — ServiceHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .brand-gradient { background: linear-gradient(135deg, #0D4A3E 0%, #1A6B5A 50%, #14B8A6 100%); }
        .btn-primary { background: linear-gradient(135deg, #0D4A3E 0%, #1A6B5A 50%, #14B8A6 100%); transition: opacity 0.15s; }
        .btn-primary:hover { opacity: 0.92; }
        .btn-primary:active { opacity: 0.85; }
    </style>
</head>
<body class="h-full" style="background: #F7F6F2;">
<div class="min-h-full flex">

    <!-- Left panel — brand hero -->
    <div class="hidden lg:flex lg:w-[45%] flex-col relative overflow-hidden" style="background: linear-gradient(135deg, #0D4A3E 0%, #1A6B5A 55%, #14B8A6 100%);">
        <!-- Subtle glow blobs -->
        <div class="absolute top-0 left-0 w-96 h-96 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2" style="background: rgba(45,212,191,0.12);"></div>
        <div class="absolute bottom-0 right-0 w-80 h-80 rounded-full blur-3xl translate-x-1/2 translate-y-1/2" style="background: rgba(245,200,66,0.08);"></div>

        <div class="relative flex flex-col justify-between h-full p-12">
            <!-- Logo -->
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center shadow-lg" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2);">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <span class="text-white font-bold text-lg tracking-tight">ServiceHub</span>
            </div>

            <!-- Hero text -->
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold mb-6" style="background: rgba(245,200,66,0.15); color: #F5C842; border: 1px solid rgba(245,200,66,0.25);">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#F5C842] animate-pulse"></span>
                    AI-Powered · Karachi
                </div>
                <h2 class="text-white text-4xl font-bold leading-tight mb-4">
                    Home services,<br>
                    <span style="color: #2DD4BF;">booked by AI</span>
                </h2>
                <p class="text-sm leading-relaxed mb-10 max-w-xs" style="color: rgba(213,245,240,0.75);">
                    Describe your need in Urdu, Roman Urdu, or English — our 6-agent AI pipeline finds the right provider and books it instantly.
                </p>

                <!-- Stats -->
                <div class="grid grid-cols-3 gap-3">
                    <div class="rounded-xl p-4" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.12);">
                        <div class="text-2xl font-bold" style="color: #2DD4BF;">14+</div>
                        <div class="text-xs mt-1" style="color: rgba(213,245,240,0.6);">Verified Providers</div>
                    </div>
                    <div class="rounded-xl p-4" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.12);">
                        <div class="text-2xl font-bold" style="color: #F5C842;">6</div>
                        <div class="text-xs mt-1" style="color: rgba(213,245,240,0.6);">AI Agents</div>
                    </div>
                    <div class="rounded-xl p-4" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.12);">
                        <div class="text-2xl font-bold text-white">31</div>
                        <div class="text-xs mt-1" style="color: rgba(213,245,240,0.6);">API Endpoints</div>
                    </div>
                </div>
            </div>

            <p class="text-xs" style="color: rgba(213,245,240,0.4);">Hackathon Demo · Karachi, Pakistan</p>
        </div>
    </div>

    <!-- Right panel — form -->
    <div class="flex-1 flex items-center justify-center p-8 bg-white">
        <div class="w-full max-w-sm">
            <!-- Mobile logo -->
            <div class="flex items-center gap-2.5 mb-8 lg:hidden">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center brand-gradient shadow">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <span class="font-bold text-[#0B1220]">ServiceHub</span>
            </div>
            @yield('content')
        </div>
    </div>
</div>
</body>
</html>
