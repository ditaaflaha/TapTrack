<x-app-layout>
    <div class="mb-6 flex justify-between items-center flex-wrap gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Absensi Tap</h2>
            <p class="text-gray-500 text-sm mt-1">Pantau kehadiran harian dan riwayat keterlambatan karyawan.</p>
        </div>
        <a href="{{ route('attendances.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-xl shadow-md transition-all flex items-center">
            <i class="ph ph-plus mr-2"></i> Rekam Absensi Manual
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-red-50 border border-red-200 p-4 rounded-2xl flex items-center">
            <div class="w-12 h-12 bg-red-100 text-red-500 rounded-xl flex items-center justify-center mr-4">
                <i class="ph-fill ph-warning-circle text-2xl"></i>
            </div>
            <div>
                <h4 class="font-bold text-red-700">Total Keterlambatan</h4>
                <p class="text-sm text-red-600">Terdeteksi <strong>{{ $totalLate }}</strong> absensi terlambat dari hasil filter.</p>
            </div>
        </div>
        <div class="bg-blue-50 border border-blue-200 p-4 rounded-2xl flex items-center">
            <div class="w-12 h-12 bg-blue-100 text-blue-500 rounded-xl flex items-center justify-center mr-4">
                <i class="ph-fill ph-clock-counter-clockwise text-2xl"></i>
            </div>
            <div>
                <h4 class="font-bold text-blue-700">Total Jam Lembur</h4>
                <p class="text-sm text-blue-600">Karyawan mengumpulkan <strong>{{ $totalOvertimeSum }} jam</strong> lembur di rentang filter ini.</p>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 mb-6">
        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-4 flex items-center">
            <i class="ph ph-funnel mr-2 text-indigo-500"></i> Filter Data Kehadiran
        </h3>
        <form method="GET" action="{{ route('attendances.index') }}" class="flex flex-col md:flex-row gap-4 items-end" id="filterForm">
            <div class="w-full md:w-1/4">
                <label class="block text-xs font-semibold text-gray-500 mb-2">Tipe Filter</label>
                <select name="filter_type" id="filter_type" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-gray-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors" onchange="updateFilterInputs()">
                    <option value="">Bulan Ini (Default)</option>
                    <option value="daily" {{ request('filter_type') == 'daily' ? 'selected' : '' }}>Harian</option>
                    <option value="weekly" {{ request('filter_type') == 'weekly' ? 'selected' : '' }}>Mingguan</option>
                    <option value="monthly" {{ request('filter_type') == 'monthly' ? 'selected' : '' }}>Bulanan</option>
                    <option value="yearly" {{ request('filter_type') == 'yearly' ? 'selected' : '' }}>Tahunan</option>
                    <option value="custom" {{ request('filter_type') == 'custom' ? 'selected' : '' }}>Rentang Tanggal Khusus</option>
                </select>
            </div>
            
            <div class="w-full md:w-1/4 filter-input" id="input_daily" style="display: none;">
                <label class="block text-xs font-semibold text-gray-500 mb-2">Pilih Tanggal</label>
                <input type="date" name="date" value="{{ request('date', date('Y-m-d')) }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-gray-800">
            </div>

            <div class="w-full md:w-1/4 filter-input" id="input_weekly" style="display: none;">
                <label class="block text-xs font-semibold text-gray-500 mb-2">Pilih Titik Akhir 7 Tanggal</label>
                <input type="date" name="week_date" value="{{ request('week_date', date('Y-m-d')) }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-gray-800">
            </div>

            <div class="w-full md:w-1/4 filter-input" id="input_monthly" style="display: none;">
                <label class="block text-xs font-semibold text-gray-500 mb-2">Pilih Bulan</label>
                <input type="month" name="month" value="{{ request('month', date('Y-m')) }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-gray-800">
            </div>

            <div class="w-full md:w-1/4 filter-input" id="input_yearly" style="display: none;">
                <label class="block text-xs font-semibold text-gray-500 mb-2">Pilih Tahun</label>
                <input type="number" name="year" min="2000" max="2099" step="1" value="{{ request('year', date('Y')) }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-gray-800">
            </div>

            <div class="w-full md:w-1/2 filter-input flex gap-4" id="input_custom" style="display: none;">
                <div class="w-1/2">
                    <label class="block text-xs font-semibold text-gray-500 mb-2">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-gray-800">
                </div>
                <div class="w-1/2">
                    <label class="block text-xs font-semibold text-gray-500 mb-2">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-gray-800">
                </div>
            </div>

            <div class="flex space-x-2 w-full md:w-auto mt-2">
                <button type="submit" class="bg-indigo-50 text-indigo-600 hover:bg-indigo-100 font-medium py-2.5 px-6 rounded-xl transition-colors">Terapkan</button>
                @if(request()->has('filter_type'))
                    <a href="{{ route('attendances.index') }}" class="bg-gray-100 text-gray-600 hover:bg-gray-200 font-medium py-2.5 px-4 rounded-xl transition-colors">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-max">
                <thead>
                    <tr class="text-xs text-gray-400 border-b border-gray-100 uppercase tracking-wider">
                        <th class="pb-3 font-medium px-4">Karyawan</th>
                        <th class="pb-3 font-medium px-4">Tanggal</th>
                        <th class="pb-3 font-medium px-4 text-center">Tap In</th>
                        <th class="pb-3 font-medium px-4 text-center">Tap Out</th>
                        <th class="pb-3 font-medium px-4 text-center">Status</th>
                        <th class="pb-3 font-medium px-4 text-center">Lembur</th>
                        <th class="pb-3 font-medium px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($attendances as $attn)
                    <tr class="border-b border-gray-50 last:border-0 hover:bg-gray-50 transition-colors">
                        <td class="py-4 px-4 text-gray-800">
                            <div class="font-medium flex items-center">
                                {{ $attn->employee->name ?? 'Unknown' }}
                                @if($attn->is_manual)
                                    <span class="ml-2 bg-yellow-100 text-yellow-700 text-[10px] uppercase font-bold px-2 py-0.5 rounded-md flex items-center" title="Input Manual (Mesin Error)">
                                        <i class="ph-fill ph-hand-pointing mr-1"></i> Manual
                                    </span>
                                @endif
                            </div>
                            <div class="text-xs text-gray-500">{{ $attn->employee->nik ?? '-' }}</div>
                        </td>
                        <td class="py-4 px-4 text-gray-600">{{ \Carbon\Carbon::parse($attn->date)->format('d M Y') }}</td>
                        <td class="py-4 px-4 text-center text-gray-800 font-semibold">
                            {{ $attn->tap_in ? \Carbon\Carbon::parse($attn->tap_in)->format('H:i') : '--:--' }}
                        </td>
                        <td class="py-4 px-4 text-center text-gray-800 font-semibold">
                            {{ $attn->tap_out ? \Carbon\Carbon::parse($attn->tap_out)->format('H:i') : '--:--' }}
                        </td>
                        <td class="py-4 px-4 text-center">
                            @if($attn->status == 'Tepat Waktu')
                                <span class="bg-green-50 border border-green-200 text-green-600 px-3 py-1 rounded-full text-xs font-semibold flex items-center justify-center w-max mx-auto">
                                    <i class="ph-fill ph-check-circle mr-1"></i> Tepat Waktu
                                </span>
                            @else
                                <span class="bg-red-50 border border-red-200 text-red-600 px-3 py-1 rounded-full text-xs font-semibold flex items-center justify-center w-max mx-auto">
                                    <i class="ph-fill ph-warning-circle mr-1"></i> Terlambat
                                </span>
                            @endif
                        </td>
                        <td class="py-4 px-4 text-center">
                            @if($attn->overtime_hours > 0)
                                <span class="bg-blue-50 text-blue-600 font-bold px-3 py-1 rounded-xl text-xs">{{ $attn->overtime_hours }} Jam</span>
                            @else
                                <span class="text-gray-400 text-xs">-</span>
                            @endif
                        </td>
                        <td class="py-4 px-4 text-center">
                            <div class="flex justify-center space-x-2">
                                <a href="{{ route('attendances.edit', $attn->id) }}" class="p-2 text-indigo-500 hover:bg-indigo-50 rounded-lg transition-colors" title="Edit">
                                    <i class="ph ph-pencil-simple text-lg"></i>
                                </a>
                                <form action="{{ route('attendances.destroy', $attn->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
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
                        <td colspan="7" class="py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <i class="ph ph-calendar-check text-4xl mb-3 text-gray-300"></i>
                                Belum ada riwayat absensi.
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-6">
            {{ $attendances->links() }}
        </div>
    </div>

    <script>
        function updateFilterInputs() {
            document.querySelectorAll('.filter-input').forEach(el => el.style.display = 'none');
            const val = document.getElementById('filter_type').value;
            if(val) {
                const target = document.getElementById('input_' + val);
                if(target) target.style.display = (val === 'custom') ? 'flex' : 'block';
            }
        }
        document.addEventListener('DOMContentLoaded', updateFilterInputs);
    </script>
</x-app-layout>
