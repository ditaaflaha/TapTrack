<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartForce - Industrial Monitoring & IoT</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>

<body class="bg-slate-50 font-sans antialiased">

    <nav class="bg-white/80 backdrop-blur-md sticky top-0 z-50 border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-2">
                    <div class="bg-blue-600 p-2 rounded-lg text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04m17.236 0a11.955 11.955 0 00-11.76 7.92 11.959 11.959 0 01-8.618 3.04" />
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-slate-800 tracking-tight">SmartForce <span
                            class="text-blue-600">IoT</span></span>
                </div>
                <div class="hidden md:flex space-x-8 text-sm font-medium text-slate-600">
                    <a href="#features" class="hover:text-blue-600 transition">Fitur</a>
                    <a href="#how-it-works" class="hover:text-blue-600 transition">Cara Kerja</a>
                    <a href="#" class="hover:text-blue-600 transition">Dashboard</a>
                </div>
                <div>
                    <a href="/login"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-full text-sm font-semibold transition shadow-lg shadow-blue-200">
                        Masuk Sistem
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <header class="relative overflow-hidden pt-16 pb-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center">
                <span
                    class="inline-block py-1 px-3 rounded-full bg-blue-100 text-blue-700 text-xs font-bold uppercase tracking-wider mb-4">
                    Solusi Industri 4.0
                </span>
                <h1 class="text-5xl md:text-6xl font-extrabold text-slate-900 mb-6 leading-tight">
                    Monitoring Tenaga Kerja <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-500">Berbasis
                        IoT Real-Time</span>
                </h1>
                <p class="text-lg text-slate-600 max-w-2xl mx-auto mb-10">
                    Integrasi kontrol akses RFID dan pemantauan produktivitas dalam satu platform terpusat. Tingkatkan
                    keamanan dan efisiensi operasional industri Anda.
                </p>
                <div class="flex flex-wrap justify-center gap-4">
                    <button
                        class="px-8 py-4 bg-slate-900 text-white rounded-xl font-bold hover:bg-slate-800 transition transform hover:-translate-y-1">
                        Mulai Demo Sekarang
                    </button>
                    <button
                        class="px-8 py-4 bg-white text-slate-700 border border-slate-200 rounded-xl font-bold hover:bg-slate-50 transition">
                        Pelajari Hardware
                    </button>
                </div>
            </div>
        </div>
    </header>

    <section class="max-w-7xl mx-auto px-4 -mt-16 relative z-20">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-8 rounded-2xl shadow-xl border border-slate-100 flex items-center gap-5">
                <div class="bg-green-100 p-4 rounded-xl text-green-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-slate-500 text-sm font-medium">Tenaga Kerja Aktif</p>
                    <h3 class="text-3xl font-bold text-slate-800">1,284</h3>
                </div>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-xl border border-slate-100 flex items-center gap-5">
                <div class="bg-blue-100 p-4 rounded-xl text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04m17.236 0a11.955 11.955 0 00-11.76 7.92 11.959 11.959 0 01-8.618 3.04" />
                    </svg>
                </div>
                <div>
                    <p class="text-slate-500 text-sm font-medium">Akses IoT Hari Ini</p>
                    <h3 class="text-3xl font-bold text-slate-800">5,032</h3>
                </div>
            </div>
            <div class="bg-white p-8 rounded-2xl shadow-xl border border-slate-100 flex items-center gap-5">
                <div class="bg-red-100 p-4 rounded-xl text-red-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <p class="text-slate-500 text-sm font-medium">Peringatan Keamanan</p>
                    <h3 class="text-3xl font-bold text-slate-800">0</h3>
                </div>
            </div>
        </div>
    </section>

</body>

</html>