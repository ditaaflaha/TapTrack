<x-app-layout>
    <!-- Welcome Hero Section -->
    <div class="mb-8 p-6 bg-white rounded-3xl border border-gray-150 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4 relative overflow-hidden">
        <div class="absolute -right-20 -top-20 w-60 h-60 bg-indigo-50 rounded-full opacity-50 z-0"></div>
        <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-indigo-50 rounded-full opacity-30 z-0"></div>
        
        <div class="relative z-10">
            <h2 class="text-3xl font-extrabold text-gray-800 tracking-tight">Selamat Datang Kembali, Admin!</h2>
            <p class="text-gray-500 text-sm mt-1.5 flex items-center">
                <i class="ph ph-calendar-blank text-indigo-500 text-lg mr-2"></i>
                Hari ini: <strong class="text-gray-700 ml-1">{{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}</strong>
            </p>
        </div>
        
        <div class="relative z-10 bg-indigo-50 border border-indigo-100 rounded-2xl px-4 py-3 flex items-center shadow-inner">
            <div class="w-2.5 h-2.5 bg-emerald-500 rounded-full animate-ping mr-2.5"></div>
            <span class="text-xs font-bold text-indigo-800 uppercase tracking-wider">Sistem Monitoring Aktif</span>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Karyawan -->
        <div class="relative bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-3xl p-6 text-white overflow-hidden shadow-lg shadow-indigo-100 hover:shadow-xl transition-all duration-300">
            <div class="absolute -bottom-4 -right-4 w-28 h-28 bg-white/10 rounded-2xl transform rotate-12 backdrop-blur-sm"></div>
            <h3 class="text-indigo-100 font-medium text-sm mb-1 relative z-10">Total Karyawan</h3>
            <div class="text-4xl font-extrabold mb-1 relative z-10">{{ $totalEmployees }}</div>
            <p class="text-xs text-indigo-100/80 relative z-10">Karyawan aktif terdaftar</p>
        </div>

        <!-- Canteen Today -->
        <div class="relative bg-gradient-to-br from-emerald-500 to-teal-600 rounded-3xl p-6 text-white overflow-hidden shadow-lg shadow-teal-100 hover:shadow-xl transition-all duration-300">
            <div class="absolute -bottom-4 -right-4 w-28 h-28 bg-white/10 rounded-2xl transform rotate-12 backdrop-blur-sm"></div>
            <h3 class="text-emerald-100 font-medium text-sm mb-1 relative z-10">Kantin Hari Ini</h3>
            <div class="text-4xl font-extrabold mb-1 relative z-10">Rp {{ number_format($totalCanteenToday, 0, ',', '.') }}</div>
            <p class="text-xs text-emerald-100/80 relative z-10">Nominal transaksi makan siang</p>
        </div>

        <!-- Attendance Today -->
        <div class="relative bg-gradient-to-br from-amber-500 to-orange-600 rounded-3xl p-6 text-white overflow-hidden shadow-lg shadow-orange-100 hover:shadow-xl transition-all duration-300">
            <div class="absolute -bottom-4 -right-4 w-28 h-28 bg-white/10 rounded-2xl transform rotate-12 backdrop-blur-sm"></div>
            <h3 class="text-amber-100 font-medium text-sm mb-1 relative z-10">Absensi Hari Ini</h3>
            <div class="text-4xl font-extrabold mb-1 relative z-10">{{ $totalAttendanceToday }}</div>
            <p class="text-xs text-amber-100/80 relative z-10">Karyawan melakukan tap hadir</p>
        </div>

        <!-- Estimated Inside -->
        <div class="relative bg-gradient-to-br from-violet-500 to-fuchsia-600 rounded-3xl p-6 text-white overflow-hidden shadow-lg shadow-violet-100 hover:shadow-xl transition-all duration-300">
            <div class="absolute -bottom-4 -right-4 w-28 h-28 bg-white/10 rounded-2xl transform rotate-12 backdrop-blur-sm"></div>
            <h3 class="text-violet-100 font-medium text-sm mb-1 relative z-10">Di Dalam Perusahaan</h3>
            <div class="text-4xl font-extrabold mb-1 relative z-10">{{ $employeesInsideCount }}</div>
            <p class="text-xs text-violet-100/80 relative z-10">Estimasi keberadaan karyawan</p>
        </div>
    </div>

    <!-- Main Section (Rolling Door Control & Quick Actions) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Rolling Door Card (Interactive) -->
        <div class="lg:col-span-2 bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex flex-col justify-between relative overflow-hidden group">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h3 class="text-gray-500 font-bold text-xs uppercase tracking-wider mb-1">Akses Pintu Utama</h3>
                    <h2 class="text-2xl font-black text-gray-800">Rolling Door Control</h2>
                </div>
                <!-- Dynamic Status Pill -->
                <div id="gateStatusBadge" class="px-4 py-1.5 rounded-full text-xs font-bold flex items-center transition-all duration-300">
                    <span id="gateStatusIndicator" class="w-2 h-2 rounded-full mr-2 animate-pulse"></span>
                    <span id="gateStatusText">Loading...</span>
                </div>
            </div>

            <!-- Visual representation of the gate -->
            <div class="my-4 py-8 bg-[#f8fafc] rounded-2xl border border-dashed border-gray-200 flex flex-col items-center justify-center relative overflow-hidden">
                <div id="gateVisualBorder" class="w-36 h-36 rounded-full flex items-center justify-center transition-all duration-500 shadow-lg">
                    <i id="gateIcon" class="ph-fill text-6xl transition-all duration-500"></i>
                </div>
                <div class="text-center mt-4">
                    <p id="gateDescription" class="text-xs text-gray-400 font-semibold px-6"></p>
                </div>
            </div>

            <!-- Controller Action Button -->
            <button id="toggleGateBtn" class="w-full text-white font-extrabold text-xs py-4 rounded-2xl shadow-md transition-all duration-300 uppercase tracking-widest active:scale-[0.98]">
                Buka / Kunci Rolling Door
            </button>
        </div>

        <!-- Quick Actions Panel -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex flex-col justify-between">
            <div>
                <h3 class="text-gray-500 font-bold text-xs uppercase tracking-wider mb-4">Akses Cepat Admin</h3>
                <div class="grid grid-cols-1 gap-3">
                    <a href="{{ route('employees.create') }}" class="group/btn flex items-center p-3 bg-gray-50 hover:bg-indigo-50 border border-gray-100 hover:border-indigo-100 rounded-2xl transition-all duration-300">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 group-hover/btn:bg-white text-indigo-500 flex items-center justify-center mr-3 shadow-sm transition-colors">
                            <i class="ph ph-user-plus text-lg"></i>
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-gray-800 group-hover/btn:text-indigo-650 transition-colors">Tambah Karyawan</span>
                            <span class="block text-[11px] text-gray-400">Daftarkan profil baru ke database</span>
                        </div>
                    </a>

                    <a href="{{ route('attendances.create') }}" class="group/btn flex items-center p-3 bg-gray-50 hover:bg-emerald-50 border border-gray-100 hover:border-emerald-100 rounded-2xl transition-all duration-300">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 group-hover/btn:bg-white text-emerald-500 flex items-center justify-center mr-3 shadow-sm transition-colors">
                            <i class="ph ph-calendar-plus text-lg"></i>
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-gray-800 group-hover/btn:text-emerald-700 transition-colors">Absen Manual</span>
                            <span class="block text-[11px] text-gray-400">Catat kehadiran secara manual</span>
                        </div>
                    </a>

                    <a href="{{ route('canteen-transactions.create') }}" class="group/btn flex items-center p-3 bg-gray-50 hover:bg-rose-50 border border-gray-100 hover:border-rose-100 rounded-2xl transition-all duration-300">
                        <div class="w-10 h-10 rounded-xl bg-rose-50 group-hover/btn:bg-white text-rose-500 flex items-center justify-center mr-3 shadow-sm transition-colors">
                            <i class="ph ph-fork-knife text-lg"></i>
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-gray-800 group-hover/btn:text-rose-700 transition-colors">Tap Kantin Manual</span>
                            <span class="block text-[11px] text-gray-400">Catat transaksi flat Rp 12.000</span>
                        </div>
                    </a>

                    <a href="{{ route('employee-activities.create') }}" class="group/btn flex items-center p-3 bg-gray-50 hover:bg-amber-50 border border-gray-100 hover:border-amber-100 rounded-2xl transition-all duration-300">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 group-hover/btn:bg-white text-amber-500 flex items-center justify-center mr-3 shadow-sm transition-colors">
                            <i class="ph ph-sign-in text-lg"></i>
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-gray-800 group-hover/btn:text-amber-700 transition-colors">Tap Rolling Door</span>
                            <span class="block text-[11px] text-gray-400">Catat tap masuk/keluar rolling door</span>
                        </div>
                    </a>
                </div>
            </div>
            
            <div class="pt-4 border-t border-gray-100 mt-4 flex items-center justify-between text-xs text-gray-400 font-semibold">
                <span>Versi Aplikasi: v1.2</span>
                <span class="text-indigo-400 hover:underline cursor-pointer">Dokumentasi API</span>
            </div>
        </div>
    </div>

    <!-- Feeds Grid (Rolling Door Feed & Canteen Transactions) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Live Rolling Door Activities -->
        <div class="bg-white border border-gray-100 shadow-sm rounded-3xl p-6">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Aktivitas Rolling Door Terakhir</h3>
                    <p class="text-xs text-gray-400 mt-0.5">5 pemindaian kartu RFID fisik paling baru</p>
                </div>
                <a href="{{ route('employee-activities.index') }}" class="text-xs font-bold text-indigo-650 hover:text-indigo-800 flex items-center">
                    Lihat Semua <i class="ph ph-caret-right ml-1"></i>
                </a>
            </div>

            <div class="space-y-4">
                @forelse($latestActivities as $act)
                    <div class="flex items-center justify-between p-3.5 bg-gray-50/50 rounded-2xl border border-gray-100/50 hover:bg-gray-50 hover:border-gray-200 transition-all duration-200">
                        <div class="flex items-center overflow-hidden mr-2">
                            <!-- Avatar / Initial -->
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-sm mr-3 flex-shrink-0">
                                {{ substr($act->employee->name ?? '?', 0, 2) }}
                            </div>
                            <div class="overflow-hidden">
                                <span class="block text-sm font-bold text-gray-800 truncate">{{ $act->employee->name ?? 'Unknown' }}</span>
                                <span class="block text-[11px] text-gray-400 font-mono mt-0.5">{{ $act->employee->nik ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="flex items-center flex-shrink-0">
                            <!-- Activity badge -->
                            @if($act->activity_type === 'tap_in')
                                <span class="bg-emerald-50 text-emerald-700 border border-emerald-150 px-2.5 py-0.5 rounded-full text-[10px] font-bold flex items-center mr-3">
                                    <i class="ph ph-arrow-square-in mr-1 text-sm"></i> Masuk
                                </span>
                            @else
                                <span class="bg-amber-50 text-amber-700 border border-amber-150 px-2.5 py-0.5 rounded-full text-[10px] font-bold flex items-center mr-3">
                                    <i class="ph ph-arrow-square-out mr-1 text-sm"></i> Keluar
                                </span>
                            @endif
                            <span class="text-xs text-gray-500 font-medium">{{ \Carbon\Carbon::parse($act->scanned_at)->format('H:i') }}</span>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-gray-400">
                        <i class="ph ph-clock-counter-clockwise text-4xl mb-2 text-gray-300"></i>
                        <p class="text-sm">Belum ada aktivitas tap terdeteksi hari ini.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Latest Canteen Transactions -->
        <div class="bg-white border border-gray-100 shadow-sm rounded-3xl p-6">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Transaksi Kantin Terakhir</h3>
                    <p class="text-xs text-gray-400 mt-0.5">5 pembelian makan flat-rate paling baru</p>
                </div>
                <a href="{{ route('canteen-transactions.index') }}" class="text-xs font-bold text-indigo-650 hover:text-indigo-800 flex items-center">
                    Lihat Semua <i class="ph ph-caret-right ml-1"></i>
                </a>
            </div>

            <div class="space-y-4">
                @forelse($latestCanteen as $trx)
                    <div class="flex items-center justify-between p-3.5 bg-gray-50/50 rounded-2xl border border-gray-100/50 hover:bg-gray-50 hover:border-gray-200 transition-all duration-200">
                        <div class="flex items-center overflow-hidden mr-2">
                            <!-- Avatar / Initial -->
                            <div class="w-10 h-10 rounded-xl bg-rose-50 border border-rose-100 text-rose-500 flex items-center justify-center font-bold text-sm mr-3 flex-shrink-0">
                                {{ substr($trx->employee->name ?? '?', 0, 2) }}
                            </div>
                            <div class="overflow-hidden">
                                <span class="block text-sm font-bold text-gray-800 truncate">{{ $trx->employee->name ?? 'Unknown' }}</span>
                                <span class="block text-[11px] text-gray-400 font-mono mt-0.5">{{ $trx->employee->nik ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="flex items-center flex-shrink-0">
                            <span class="text-xs font-bold text-rose-600 mr-4">- Rp {{ number_format($trx->total_amount, 0, ',', '.') }}</span>
                            <span class="text-xs text-gray-400 font-medium">{{ \Carbon\Carbon::parse($trx->transaction_date)->format('d M') }}</span>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-gray-400">
                        <i class="ph ph-receipt text-4xl mb-2 text-gray-300"></i>
                        <p class="text-sm">Belum ada transaksi kantin terdaftar.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- JS code for interactive gate controller simulation -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const gateStatusText = document.getElementById('gateStatusText');
            const gateStatusIndicator = document.getElementById('gateStatusIndicator');
            const gateStatusBadge = document.getElementById('gateStatusBadge');
            const gateIcon = document.getElementById('gateIcon');
            const gateVisualBorder = document.getElementById('gateVisualBorder');
            const gateDescription = document.getElementById('gateDescription');
            const toggleGateBtn = document.getElementById('toggleGateBtn');

            // Load initial state or set default to Locked
            let isLocked = localStorage.getItem('rolling_door_locked');
            if (isLocked === null) {
                isLocked = 'true';
            }

            function updateUI(locked) {
                if (locked === 'true') {
                    // Update to Locked visual
                    gateStatusBadge.className = "px-4 py-1.5 rounded-full text-xs font-bold flex items-center bg-rose-50 border border-rose-200 text-rose-700";
                    gateStatusIndicator.className = "w-2 h-2 rounded-full mr-2 bg-rose-500 animate-pulse";
                    gateStatusText.textContent = "TERKUNCI";
                    
                    gateVisualBorder.className = "w-36 h-36 rounded-full flex items-center justify-center shadow-lg bg-rose-50 border-4 border-rose-500 scale-100";
                    gateIcon.className = "ph-fill ph-lock text-6xl text-rose-500 transition-all duration-500";
                    gateDescription.textContent = "Akses fisik gerbang rolling door dalam kondisi dikunci secara elektronik. Karyawan hanya bisa lewat dengan menempelkan kartu RFID valid.";
                    
                    toggleGateBtn.className = "w-full bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-extrabold text-xs py-4 rounded-2xl shadow-md shadow-teal-150 transition-all duration-300 uppercase tracking-widest active:scale-[0.98]";
                    toggleGateBtn.textContent = "BUKA ROLLING DOOR";
                } else {
                    // Update to Open visual
                    gateStatusBadge.className = "px-4 py-1.5 rounded-full text-xs font-bold flex items-center bg-emerald-50 border border-emerald-200 text-emerald-700";
                    gateStatusIndicator.className = "w-2 h-2 rounded-full mr-2 bg-emerald-500 animate-ping";
                    gateStatusText.textContent = "TERBUKA (BEBAS)";
                    
                    gateVisualBorder.className = "w-36 h-36 rounded-full flex items-center justify-center shadow-lg bg-emerald-50 border-4 border-emerald-500 scale-105";
                    gateIcon.className = "ph-fill ph-lock-open text-6xl text-emerald-500 transition-all duration-500";
                    gateDescription.textContent = "Akses fisik gerbang rolling door dibuka paksa secara elektronik. Gerbang dalam mode bebas dilewati tanpa scan RFID.";
                    
                    toggleGateBtn.className = "w-full bg-gradient-to-r from-rose-500 to-red-600 hover:from-rose-600 hover:to-red-700 text-white font-extrabold text-xs py-4 rounded-2xl shadow-md shadow-rose-150 transition-all duration-300 uppercase tracking-widest active:scale-[0.98]";
                    toggleGateBtn.textContent = "KUNCI ROLLING DOOR";
                }
            }

            // Init call
            updateUI(isLocked);

            // Click listener
            toggleGateBtn.addEventListener('click', () => {
                // Add click effect / disable state briefly to simulate network call
                toggleGateBtn.disabled = true;
                gateDescription.textContent = "Sedang mengirimkan sinyal kendali ke gerbang elektronik...";
                gateVisualBorder.classList.add('animate-pulse');

                setTimeout(() => {
                    isLocked = (isLocked === 'true') ? 'false' : 'true';
                    localStorage.setItem('rolling_door_locked', isLocked);
                    updateUI(isLocked);
                    
                    toggleGateBtn.disabled = false;
                    gateVisualBorder.classList.remove('animate-pulse');
                }, 800);
            });
        });
    </script>
</x-app-layout>
