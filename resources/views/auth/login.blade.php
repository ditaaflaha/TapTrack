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
<body class="font-sans antialiased text-slate-900 bg-slate-50 min-h-screen flex flex-col sm:justify-center items-center py-8 px-4 relative overflow-x-hidden">
    <!-- Ambient Glow Background -->
    <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none">
        <div class="absolute top-[50%] left-[50%] -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] rounded-full bg-gradient-to-tr from-purple-200/30 to-indigo-200/30 blur-3xl opacity-80"></div>
    </div>

    <!-- Content Container -->
    <div class="relative z-10 w-full max-w-[420px] my-6 flex flex-col items-center">
        <!-- Laravel Top Wireframe Logo -->
        <div class="mb-6 text-center">
            <x-application-logo class="w-14 h-14 text-slate-300 fill-current mx-auto" />
        </div>

        <!-- Login Card -->
        <div class="w-full bg-white shadow-2xl shadow-indigo-100/40 border border-slate-100 rounded-[2rem] px-8 py-10 sm:px-10">
            <!-- App Logo & Title -->
            <div class="text-center mb-8">
                <!-- App Logo Image -->
                <div class="w-20 h-20 mx-auto rounded-2xl overflow-hidden shadow-md mb-5 border border-slate-100 flex items-center justify-center bg-[#004785]">
                    <img src="{{ asset('images/logo.svg') }}" alt="TapTrack Logo" class="w-full h-full object-cover" />
                </div>
                
                <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight">TapTrack</h2>
                <p class="text-xs font-semibold text-slate-400 italic mt-1.5">Integrated IoT & Presence System</p>
            </div>

            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <!-- Username -->
                <div>
                    <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">Username</label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <input id="name" class="pl-11 block w-full rounded-xl border-slate-200 bg-slate-50/50 text-slate-800 placeholder-slate-400 focus:border-indigo-500 focus:ring focus:ring-indigo-200/50 focus:bg-white transition-all duration-200 py-3 text-sm" 
                               type="text" name="name" :value="old('name')" required autofocus placeholder="Enter your username" />
                    </div>
                    <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
                </div>

                <!-- Password -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label for="password" class="text-sm font-semibold text-slate-700">Password</label>
                        @if (Route::has('password.request'))
                            <a class="text-xs font-bold text-indigo-600 hover:text-indigo-700 hover:underline transition-colors" href="{{ route('password.request') }}">
                                Lupa sandi?
                            </a>
                        @endif
                    </div>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input id="password" class="pl-11 block w-full rounded-xl border-slate-200 bg-slate-50/50 text-slate-800 placeholder-slate-400 focus:border-indigo-500 focus:ring focus:ring-indigo-200/50 focus:bg-white transition-all duration-200 py-3 text-sm"
                                type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
                </div>

                <!-- Remember Me -->
                <div class="flex items-center">
                    <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                        <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-all cursor-pointer">
                        <span class="ms-2.5 text-xs text-slate-500 group-hover:text-slate-700 transition-colors font-medium">Biarkan saya tetap masuk</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit" class="w-full py-3.5 px-4 bg-[#4e3df5] hover:bg-[#4335d9] active:bg-[#392cb3] text-white font-bold rounded-xl shadow-lg shadow-indigo-100 hover:shadow-indigo-200 focus:outline-none focus:ring-4 focus:ring-indigo-500/20 transition-all duration-300 tracking-wider text-sm">
                        SIGN IN
                    </button>
                </div>
            </form>
            
            <!-- Register Footer -->
            @if (Route::has('register'))
                <p class="mt-8 text-center text-xs font-semibold text-slate-400">
                    Belum punya akun? 
                    <a href="{{ route('register') }}" class="text-indigo-600 hover:text-indigo-700 hover:underline ml-1">
                        Daftar sekarang
                    </a>
                </p>
            @endif
        </div>
    </div>
</body>
</html>
