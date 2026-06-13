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
                    <div class="w-8 h-8 rounded-lg overflow-hidden flex items-center justify-center bg-[#004785] mr-3">
                        <img src="{{ asset('images/logo.svg') }}" alt="TapTrack Logo" class="w-full h-full object-cover" />
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
                        {{ __('messages.dashboard') }}
                    </a>

                    <div class="pt-4 pb-1">
                        <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('messages.hr_employees') }}</p>
                    </div>
                    <a href="{{ route('employees.index') }}" class="{{ request()->routeIs('employees.*') ? 'bg-[#252b43] text-white' : 'text-gray-400 hover:text-white hover:bg-white/5' }} flex items-center px-4 py-2.5 text-sm font-medium rounded-lg transition-colors">
                        <i class="ph ph-users text-lg mr-3 {{ request()->routeIs('employees.*') ? 'text-indigo-400' : '' }}"></i>
                        {{ __('messages.employee_list') }}
                    </a>
                    <a href="{{ route('employee-activities.index') }}" class="{{ request()->routeIs('employee-activities.*') ? 'bg-[#252b43] text-white' : 'text-gray-400 hover:text-white hover:bg-white/5' }} flex items-center px-4 py-2.5 text-sm font-medium rounded-lg transition-colors">
                        <i class="ph ph-chart-line-up text-lg mr-3 {{ request()->routeIs('employee-activities.*') ? 'text-indigo-400' : '' }}"></i>
                        {{ __('messages.employee_activities') }}
                    </a>

                    <div class="pt-4 pb-1">
                        <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('messages.attendance') }}</p>
                    </div>
                    <a href="{{ route('attendances.index') }}" class="{{ request()->routeIs('attendances.*') ? 'bg-[#252b43] text-white' : 'text-gray-400 hover:text-white hover:bg-white/5' }} flex items-center px-4 py-2.5 text-sm font-medium rounded-lg transition-colors">
                        <i class="ph ph-calendar-check text-lg mr-3 {{ request()->routeIs('attendances.*') ? 'text-indigo-400' : '' }}"></i>
                        {{ __('messages.tap_attendance') }}
                    </a>

                    <div class="pt-4 pb-1">
                        <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('messages.operations') }}</p>
                    </div>
                    <a href="{{ route('canteen-transactions.index') }}" class="{{ request()->routeIs('canteen-transactions.*') ? 'bg-[#252b43] text-white' : 'text-gray-400 hover:text-white hover:bg-white/5' }} flex items-center px-4 py-2.5 text-sm font-medium rounded-lg transition-colors">
                        <i class="ph ph-fork-knife text-lg mr-3 {{ request()->routeIs('canteen-transactions.*') ? 'text-indigo-400' : '' }}"></i>
                        {{ __('messages.canteen_transactions') }}
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
                            <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email ?? 'admin@taptrack.local' }}</p>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Main Panel -->
            <div class="flex-1 flex flex-col w-full">
                <!-- Top Header -->
                <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-end px-6 z-10 transition-all duration-300">
                    <div class="flex items-center space-x-6">
                        <!-- Language Switcher -->
                        <a href="{{ route('lang.switch', app()->getLocale() == 'id' ? 'en' : 'id') }}" class="flex items-center text-sm font-semibold text-gray-600 hover:text-indigo-600 transition-colors" title="{{ __('messages.switch_lang') }}">
                            <i class="ph ph-translate text-lg mr-1"></i> {{ __('messages.lang_code') }}
                        </a>
                        
                        <!-- Simple Profile Dropdown Mock -->
                        <div class="flex items-center space-x-3 cursor-pointer">
                            <span class="text-sm font-medium text-gray-700">{{ Auth::user()->name ?? 'Admin' }}</span>
                        </div>
                        
                        <!-- Logout Button -->
                        <form method="POST" action="{{ route('logout') }}" class="flex items-center border-l pl-6 border-gray-200">
                            @csrf
                            <button type="submit" class="flex items-center text-sm font-medium text-red-500 hover:text-red-700 transition-colors">
                                <i class="ph ph-sign-out text-lg mr-1"></i> {{ __('messages.logout') }}
                            </button>
                        </form>
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
