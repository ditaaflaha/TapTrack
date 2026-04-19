<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - {{ config('app.name', 'TapTrack') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-gray-900 bg-gray-100 min-h-screen relative overflow-hidden">
    <!-- Background Decoration -->
    <div class="fixed top-0 left-0 w-full h-full z-0 overflow-hidden bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500">
        <div class="absolute -top-40 -left-40 w-96 h-96 rounded-full bg-white opacity-20 blur-3xl"></div>
        <div class="absolute top-1/2 left-1/4 w-80 h-80 rounded-full bg-blue-300 opacity-20 blur-3xl mix-blend-multiply"></div>
        <div class="absolute -bottom-40 -right-40 w-96 h-96 rounded-full bg-pink-300 opacity-30 blur-3xl"></div>
    </div>

    <div class="relative z-10 min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
        <!-- Logo Area -->
        <div class="mb-8 text-center text-white">
            <h1 class="text-5xl font-extrabold tracking-wider drop-shadow-md">TapTrack</h1>
            <p class="mt-2 text-lg font-medium opacity-90 drop-shadow-sm">Sign in to your account</p>
        </div>

        <!-- Login Card -->
        <div class="w-full sm:max-w-md px-10 py-10 bg-white/10 backdrop-blur-xl border border-white/20 shadow-2xl overflow-hidden sm:rounded-3xl relative">
            
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <!-- Username -->
                <div>
                    <label for="name" class="block text-sm font-semibold text-white mb-2">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input id="name" class="pl-10 block w-full rounded-xl bg-white/20 border border-white/30 text-white placeholder-gray-200 focus:ring-4 focus:ring-white/20 focus:border-white focus:outline-none transition-all duration-300 py-3" type="text" name="name" :value="old('name')" required autofocus placeholder="Enter your username" />
                    </div>
                    <x-input-error :messages="$errors->get('name')" class="mt-2 text-pink-200 bg-red-900/40 p-2 rounded-lg text-sm" />
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-white mb-2">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input id="password" class="pl-10 block w-full rounded-xl bg-white/20 border border-white/30 text-white placeholder-gray-200 focus:ring-4 focus:ring-white/20 focus:border-white focus:outline-none transition-all duration-300 py-3"
                                type="password"
                                name="password"
                                required autocomplete="current-password" placeholder="••••••••" />
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2 text-pink-200 bg-red-900/40 p-2 rounded-lg text-sm" />
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between text-sm">
                    <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                        <input id="remember_me" type="checkbox" class="rounded border-none bg-white/20 text-indigo-500 shadow-sm focus:ring-white/30 focus:ring-offset-0 transition-all cursor-pointer" name="remember">
                        <span class="ms-2 text-white group-hover:text-indigo-100 transition-colors">Remember me</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a class="text-white hover:text-indigo-200 transition-colors hover:underline" href="{{ route('password.request') }}">
                            Forgot password?
                        </a>
                    @endif
                </div>

                <div class="mt-8">
                    <button type="submit" class="w-full py-3 px-4 bg-white text-indigo-600 font-bold rounded-xl shadow-lg hover:bg-indigo-50 focus:outline-none focus:ring-4 focus:ring-indigo-300/50 transition-all duration-300 transform hover:-translate-y-0.5 active:translate-y-0">
                        Sign In
                    </button>
                </div>
            </form>
        </div>
        
        <div class="mt-8 text-center text-white/70 text-sm">
            &copy; {{ date('Y') }} TapTrack. All rights reserved.
        </div>
    </div>
</body>
</html>
