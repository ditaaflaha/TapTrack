<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'TapTrack') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <!-- Icons via CDN -->
        <script src="https://unpkg.com/@phosphor-icons/web"></script>
    </head>
    <body class="font-sans antialiased bg-[#f4f7f6]">
        <div class="flex h-screen overflow-hidden">
            <!-- Sidebar -->
            <aside class="w-64 bg-[#1a1d2d] flex-shrink-0 hidden md:flex flex-col text-gray-300 transition-all duration-300">
                <!-- Logo -->
                <div class="h-16 flex items-center px-6 border-b border-gray-800/60">
                    <div class="w-8 h-8 rounded-lg bg-indigo-500 flex items-center justify-center text-white font-bold text-sm mr-3">
                        SG
                    </div>
                    <span class="text-white font-bold text-xl tracking-wide">TapTrack</span>
                    <button class="ml-auto text-gray-400 hover:text-white">
                        <i class="ph ph-list text-xl"></i>
                    </button>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'bg-[#252b43] text-white' : 'text-gray-400 hover:text-white hover:bg-white/5' }} flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors">
                        <i class="ph ph-house text-lg mr-3 {{ request()->routeIs('dashboard') ? 'text-indigo-400' : '' }}"></i>
                        Dashboard
                    </a>

                    <div class="pt-4 pb-1">
                        <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">HR & PEGAWAI</p>
                    </div>
                    <a href="{{ route('employees.index') }}" class="{{ request()->routeIs('employees.*') ? 'bg-[#252b43] text-white' : 'text-gray-400 hover:text-white hover:bg-white/5' }} flex items-center px-4 py-2.5 text-sm font-medium rounded-lg transition-colors">
                        <i class="ph ph-users text-lg mr-3 {{ request()->routeIs('employees.*') ? 'text-indigo-400' : '' }}"></i>
                        Daftar Karyawan
                    </a>
                    <a href="#" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg text-gray-400 hover:text-white hover:bg-white/5 transition-colors">
                        <i class="ph ph-chart-line-up text-lg mr-3"></i>
                        Aktivitas Karyawan
                    </a>

                    <div class="pt-4 pb-1">
                        <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">KEHADIRAN</p>
                    </div>
                    <a href="#" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg text-gray-400 hover:text-white hover:bg-white/5 transition-colors">
                        <i class="ph ph-calendar-check text-lg mr-3"></i>
                        Absensi Tap
                    </a>

                    <div class="pt-4 pb-1">
                        <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">OPERASIONAL</p>
                    </div>
                    <a href="#" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg text-gray-400 hover:text-white hover:bg-white/5 transition-colors">
                        <i class="ph ph-fork-knife text-lg mr-3"></i>
                        Transaksi Kantin
                    </a>
                </nav>

                <!-- User Profile Bottom -->
                <div class="p-4 border-t border-gray-800/60">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold">
                            {{ substr(Auth::user()->name ?? 'A', 0, 1) }}
                        </div>
                        <div class="ml-3 overflow-hidden">
                            <p class="text-sm font-medium text-white truncate">{{ Auth::user()->name ?? 'Admin' }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email ?? 'admin@smartgate.com' }}</p>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Main Panel -->
            <div class="flex-1 flex flex-col w-full">
                <!-- Top Header -->
                <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-end px-6 z-10 transition-all duration-300">
                    <!-- Dropdown could go here, for now a simple profile view -->
                    <div class="flex items-center space-x-3 cursor-pointer">
                        <span class="text-sm font-medium text-gray-700">{{ Auth::user()->name ?? 'Admin' }}</span>
                        <i class="ph ph-caret-down text-gray-400 text-xs"></i>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="flex-1 overflow-y-auto bg-[#f8fafc] p-6">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
