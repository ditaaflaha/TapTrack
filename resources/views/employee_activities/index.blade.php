<x-app-layout>
    <!-- Header Page -->
    <div class="mb-6 flex justify-between items-center flex-wrap gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Aktivitas Keluar Masuk</h2>
            <p class="text-gray-500 text-sm mt-1">Monitoring tap kartu RFID karyawan di gerbang rolling door secara real-time.</p>
        </div>
        <a href="{{ route('employee-activities.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-5 rounded-xl shadow-md transition-all duration-300 transform hover:-translate-y-0.5 flex items-center">
            <i class="ph ph-plus mr-2 text-lg"></i> Rekam Aktivitas Manual
        </a>
    </div>

    <!-- Alert / Success Message -->
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg shadow-sm" role="alert">
            <div class="flex items-center">
                <i class="ph-fill ph-check-circle text-xl mr-2"></i>
                <p class="font-medium">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <!-- Info Banner -->
    <div class="bg-indigo-50 border border-indigo-150 text-indigo-700 p-4 mb-6 rounded-2xl flex items-start shadow-sm">
        <i class="ph-fill ph-info text-2xl mr-3 text-indigo-500 mt-0.5"></i>
        <div>
            <h4 class="font-bold text-indigo-800">Fungsi Halaman</h4>
            <p class="text-sm text-indigo-600/90 mt-0.5">Halaman ini digunakan khusus untuk mencatat dan memonitoring akses keluar masuk fisik area perusahaan melalui pintu rolling door. Ini <strong>bukan</strong> untuk pencatatan kehadiran kerja (absensi harian).</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Tap In Hari Ini -->
        <div class="relative bg-gradient-to-br from-emerald-500 to-teal-600 rounded-3xl p-6 text-white overflow-hidden shadow-lg shadow-teal-100 transition-all duration-300 hover:shadow-xl hover:shadow-teal-200">
            <div class="absolute -bottom-4 -right-4 w-32 h-32 bg-white/10 rounded-2xl transform rotate-12 backdrop-blur-sm"></div>
            <div class="absolute -bottom-8 -right-8 w-32 h-32 bg-white/10 rounded-2xl transform rotate-12 backdrop-blur-sm"></div>
            <div class="flex justify-between items-start relative z-10">
                <div>
                    <h3 class="text-emerald-100 font-medium text-sm mb-1">Tap In Hari Ini</h3>
                    <div class="text-4xl font-extrabold mb-1">{{ $totalTapInToday }}</div>
                    <p class="text-xs text-emerald-100/80">Karyawan masuk perusahaan</p>
                </div>
                <div class="w-12 h-12 bg-white/20 backdrop-blur-md text-white rounded-2xl flex items-center justify-center shadow-inner">
                    <i class="ph-fill ph-sign-in text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Tap Out Hari Ini -->
        <div class="relative bg-gradient-to-br from-amber-500 to-orange-600 rounded-3xl p-6 text-white overflow-hidden shadow-lg shadow-orange-100 transition-all duration-300 hover:shadow-xl hover:shadow-orange-200">
            <div class="absolute -bottom-4 -right-4 w-32 h-32 bg-white/10 rounded-2xl transform rotate-12 backdrop-blur-sm"></div>
            <div class="absolute -bottom-8 -right-8 w-32 h-32 bg-white/10 rounded-2xl transform rotate-12 backdrop-blur-sm"></div>
            <div class="flex justify-between items-start relative z-10">
                <div>
                    <h3 class="text-amber-100 font-medium text-sm mb-1">Tap Out Hari Ini</h3>
                    <div class="text-4xl font-extrabold mb-1">{{ $totalTapOutToday }}</div>
                    <p class="text-xs text-amber-100/80">Karyawan keluar perusahaan</p>
                </div>
                <div class="w-12 h-12 bg-white/20 backdrop-blur-md text-white rounded-2xl flex items-center justify-center shadow-inner">
                    <i class="ph-fill ph-sign-out text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Karyawan Di Dalam (Estimasi) -->
        <div class="relative bg-gradient-to-br from-indigo-500 to-violet-600 rounded-3xl p-6 text-white overflow-hidden shadow-lg shadow-indigo-100 transition-all duration-300 hover:shadow-xl hover:shadow-indigo-200">
            <div class="absolute -bottom-4 -right-4 w-32 h-32 bg-white/10 rounded-2xl transform rotate-12 backdrop-blur-sm"></div>
            <div class="absolute -bottom-8 -right-8 w-32 h-32 bg-white/10 rounded-2xl transform rotate-12 backdrop-blur-sm"></div>
            <div class="flex justify-between items-start relative z-10">
                <div>
                    <h3 class="text-indigo-100 font-medium text-sm mb-1">Di Dalam Perusahaan</h3>
                    <div class="text-4xl font-extrabold mb-1">{{ $employeesInsideCount }}</div>
                    <p class="text-xs text-indigo-100/80">Estimasi karyawan saat ini</p>
                </div>
                <div class="w-12 h-12 bg-white/20 backdrop-blur-md text-white rounded-2xl flex items-center justify-center shadow-inner">
                    <i class="ph-fill ph-users text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Panel -->
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 mb-6">
        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-4 flex items-center">
            <i class="ph ph-funnel mr-2 text-indigo-500 text-lg"></i> Filter & Pencarian
        </h3>
        <form method="GET" action="{{ route('employee-activities.index') }}" class="flex flex-col md:flex-row gap-4 items-end" id="filterForm">
            <!-- Search Name / NIK -->
            <div class="w-full md:w-1/4">
                <label class="block text-xs font-semibold text-gray-500 mb-2">Cari Karyawan</label>
                <div class="relative">
                    <i class="ph ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama / NIK..." class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-10 pr-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition-all">
                </div>
            </div>

            <!-- Filter Type (Tap In/Out) -->
            <div class="w-full md:w-1/5">
                <label class="block text-xs font-semibold text-gray-500 mb-2">Tipe Aktivitas</label>
                <select name="activity_type" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition-all">
                    <option value="">Semua Aktivitas</option>
                    <option value="tap_in" {{ request('activity_type') == 'tap_in' ? 'selected' : '' }}>Tap In (Masuk)</option>
                    <option value="tap_out" {{ request('activity_type') == 'tap_out' ? 'selected' : '' }}>Tap Out (Keluar)</option>
                </select>
            </div>

            <!-- Filter Time Type -->
            <div class="w-full md:w-1/5">
                <label class="block text-xs font-semibold text-gray-500 mb-2">Rentang Waktu</label>
                <select name="filter_type" id="filter_type" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition-all" onchange="updateFilterInputs()">
                    <option value="">Bulan Ini (Default)</option>
                    <option value="daily" {{ request('filter_type') == 'daily' ? 'selected' : '' }}>Harian</option>
                    <option value="weekly" {{ request('filter_type') == 'weekly' ? 'selected' : '' }}>Mingguan</option>
                    <option value="monthly" {{ request('filter_type') == 'monthly' ? 'selected' : '' }}>Bulanan</option>
                    <option value="yearly" {{ request('filter_type') == 'yearly' ? 'selected' : '' }}>Tahunan</option>
                    <option value="custom" {{ request('filter_type') == 'custom' ? 'selected' : '' }}>Rentang Khusus</option>
                </select>
            </div>
            
            <!-- Filter Input Fields (Hidden/Shown dynamically) -->
            <div class="w-full md:w-1/4 filter-input" id="input_daily" style="display: none;">
                <label class="block text-xs font-semibold text-gray-500 mb-2">Pilih Tanggal</label>
                <input type="date" name="date" value="{{ request('date', date('Y-m-d')) }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 focus:bg-white focus:ring-2 focus:ring-indigo-500 transition-all">
            </div>

            <div class="w-full md:w-1/4 filter-input" id="input_weekly" style="display: none;">
                <label class="block text-xs font-semibold text-gray-500 mb-2">Titik Akhir 7 Hari</label>
                <input type="date" name="week_date" value="{{ request('week_date', date('Y-m-d')) }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 focus:bg-white focus:ring-2 focus:ring-indigo-500 transition-all">
            </div>

            <div class="w-full md:w-1/4 filter-input" id="input_monthly" style="display: none;">
                <label class="block text-xs font-semibold text-gray-500 mb-2">Pilih Bulan</label>
                <input type="month" name="month" value="{{ request('month', date('Y-m')) }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 focus:bg-white focus:ring-2 focus:ring-indigo-500 transition-all">
            </div>

            <div class="w-full md:w-1/4 filter-input" id="input_yearly" style="display: none;">
                <label class="block text-xs font-semibold text-gray-500 mb-2">Pilih Tahun</label>
                <input type="number" name="year" min="2000" max="2099" step="1" value="{{ request('year', date('Y')) }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 focus:bg-white focus:ring-2 focus:ring-indigo-500 transition-all">
            </div>

            <div class="w-full md:w-2/5 filter-input flex gap-4" id="input_custom" style="display: none;">
                <div class="w-1/2">
                    <label class="block text-xs font-semibold text-gray-500 mb-2">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 focus:bg-white focus:ring-2 focus:ring-indigo-500 transition-all">
                </div>
                <div class="w-1/2">
                    <label class="block text-xs font-semibold text-gray-500 mb-2">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 focus:bg-white focus:ring-2 focus:ring-indigo-500 transition-all">
                </div>
            </div>

            <!-- Submit and Reset Buttons -->
            <div class="flex space-x-2 w-full md:w-auto mt-2">
                <button type="submit" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-600 font-medium py-2.5 px-6 rounded-xl transition-all duration-300 w-full md:w-auto text-sm">Filter</button>
                @if(request()->has('filter_type') || request()->has('search') || request()->has('activity_type'))
                    <a href="{{ route('employee-activities.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-medium py-2.5 px-4 rounded-xl transition-all duration-300 w-full md:w-auto text-sm text-center">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Log Table -->
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-max">
                <thead>
                    <tr class="text-xs text-gray-400 border-b border-gray-100 uppercase tracking-wider">
                        <th class="pb-4 font-medium px-4">Karyawan</th>
                        <th class="pb-4 font-medium px-4">Departemen & Jabatan</th>
                        <th class="pb-4 font-medium px-4 text-center">Tipe Aktivitas</th>
                        <th class="pb-4 font-medium px-4 text-center">Waktu Pindai</th>
                        <th class="pb-4 font-medium px-4 text-center">Cara Tap</th>
                        <th class="pb-4 font-medium px-4">Catatan</th>
                        <th class="pb-4 font-medium px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($activities as $act)
                    <tr class="border-b border-gray-50 last:border-0 hover:bg-gray-50/70 transition-all duration-200">
                        <!-- Employee Info -->
                        <td class="py-4 px-4 text-gray-800">
                            <div class="font-bold text-gray-900">{{ $act->employee->name ?? 'Karyawan Terhapus' }}</div>
                            <div class="text-xs text-gray-500 font-mono mt-0.5">{{ $act->employee->nik ?? '-' }}</div>
                        </td>
                        
                        <!-- Department & Position -->
                        <td class="py-4 px-4">
                            <div class="text-gray-700 font-medium">{{ $act->employee->department ?? '-' }}</div>
                            <div class="text-xs text-gray-500 mt-0.5">{{ $act->employee->position ?? '-' }}</div>
                        </td>

                        <!-- Activity Type (Tap In/Out) -->
                        <td class="py-4 px-4 text-center">
                            @if($act->activity_type === 'tap_in')
                                <span class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-3 py-1 rounded-full text-xs font-bold inline-flex items-center justify-center">
                                    <i class="ph ph-arrow-square-in mr-1.5 text-sm"></i> Tap In (Masuk)
                                </span>
                            @else
                                <span class="bg-amber-50 border border-amber-200 text-amber-700 px-3 py-1 rounded-full text-xs font-bold inline-flex items-center justify-center">
                                    <i class="ph ph-arrow-square-out mr-1.5 text-sm"></i> Tap Out (Keluar)
                                </span>
                            @endif
                        </td>

                        <!-- Timestamp -->
                        <td class="py-4 px-4 text-center text-gray-800 font-medium">
                            {{ \Carbon\Carbon::parse($act->scanned_at)->format('d M Y, H:i') }}
                        </td>

                        <!-- Method (Manual / RFID) -->
                        <td class="py-4 px-4 text-center">
                            @if($act->is_manual)
                                <span class="bg-indigo-50 border border-indigo-150 text-indigo-700 text-[11px] uppercase font-bold px-2 py-0.5 rounded-md inline-flex items-center" title="Ditambahkan manual oleh admin">
                                    <i class="ph ph-pencil-simple mr-1"></i> Manual
                                </span>
                            @else
                                <span class="bg-gray-100 border border-gray-200 text-gray-600 text-[11px] uppercase font-bold px-2 py-0.5 rounded-md inline-flex items-center" title="Pindai RFID Gerbang Rolling Door">
                                    <i class="ph ph-scan mr-1"></i> RFID
                                </span>
                            @endif
                        </td>

                        <!-- Notes -->
                        <td class="py-4 px-4 text-gray-600 italic">
                            {{ $act->notes ?? '-' }}
                        </td>

                        <!-- Action Buttons -->
                        <td class="py-4 px-4 text-center">
                            <div class="flex justify-center space-x-2">
                                <a href="{{ route('employee-activities.edit', $act->id) }}" class="p-2 text-indigo-500 hover:bg-indigo-50 rounded-lg transition-colors" title="Edit">
                                    <i class="ph ph-pencil-simple text-lg"></i>
                                </a>
                                <form action="{{ route('employee-activities.destroy', $act->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus aktivitas ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                        <i class="ph ph-trash text-lg"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <i class="ph ph-clock-counter-clockwise text-5xl mb-4 text-gray-300"></i>
                                <h4 class="font-bold text-gray-700">Tidak ada riwayat aktivitas</h4>
                                <p class="text-sm text-gray-400 mt-1">Belum ada pemindaian tap rolling door untuk periode filter yang dipilih.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="mt-6">
            {{ $activities->appends(request()->query())->links() }}
        </div>
    </div>

    <!-- Toggle Filter Inputs Script -->
    <script>
        function updateFilterInputs() {
            document.querySelectorAll('.filter-input').forEach(el => el.style.display = 'none');
            const val = document.getElementById('filter_type').value;
            if(val) {
                const target = document.getElementById('input_' + val);
                if(target) {
                    target.style.display = (val === 'custom') ? 'flex' : 'block';
                }
            }
        }
        document.addEventListener('DOMContentLoaded', updateFilterInputs);
    </script>
</x-app-layout>
