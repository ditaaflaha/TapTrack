<x-app-layout>
    <div class="mb-6 flex justify-between items-center flex-wrap gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Transaksi Kantin</h2>
            <p class="text-gray-500 text-sm mt-1">Kelola data transaksi kantin harian karyawan.</p>
        </div>
        <a href="{{ route('canteen-transactions.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-xl shadow-md transition-all flex items-center">
            <i class="ph ph-plus mr-2"></i> Tambah Transaksi
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <div class="bg-blue-50 border border-blue-200 text-blue-700 p-4 mb-6 rounded-2xl flex items-center">
        <i class="ph-fill ph-info text-2xl mr-3 text-blue-500"></i>
        <div>
            <h4 class="font-bold">Informasi Tarif</h4>
            <p class="text-sm">Satu kali Tap dihargai sebesar <strong>Rp 12.000</strong>. Sistem akan otomatis menghitung totalnya.</p>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 mb-6">
        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-4 flex items-center">
            <i class="ph ph-funnel mr-2 text-indigo-500"></i> Filter Data
        </h3>
        <form method="GET" action="{{ route('canteen-transactions.index') }}" class="flex flex-col sm:flex-row gap-4 items-end" id="filterForm">
            <div class="w-full sm:w-1/4">
                <label class="block text-xs font-semibold text-gray-500 mb-2">Tipe Filter</label>
                <select name="filter_type" id="filter_type" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-gray-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors" onchange="updateFilterInputs()">
                    <option value="">Semua Data</option>
                    <option value="daily" {{ request('filter_type') == 'daily' ? 'selected' : '' }}>Harian</option>
                    <option value="monthly" {{ request('filter_type') == 'monthly' ? 'selected' : '' }}>Bulanan</option>
                    <option value="yearly" {{ request('filter_type') == 'yearly' ? 'selected' : '' }}>Tahunan</option>
                </select>
            </div>
            
            <div class="w-full sm:w-1/4 filter-input" id="input_daily" style="display: none;">
                <label class="block text-xs font-semibold text-gray-500 mb-2">Pilih Tanggal</label>
                <input type="date" name="date" value="{{ request('date') }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-gray-800">
            </div>

            <div class="w-full sm:w-1/4 filter-input" id="input_monthly" style="display: none;">
                <label class="block text-xs font-semibold text-gray-500 mb-2">Pilih Bulan</label>
                <input type="month" name="month" value="{{ request('month') }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-gray-800">
            </div>

            <div class="w-full sm:w-1/4 filter-input" id="input_yearly" style="display: none;">
                <label class="block text-xs font-semibold text-gray-500 mb-2">Pilih Tahun</label>
                <input type="number" name="year" min="2000" max="2099" step="1" value="{{ request('year', date('Y')) }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-gray-800" placeholder="Contoh: 2026">
            </div>

            <div class="flex space-x-2 w-full sm:w-auto">
                <button type="submit" class="bg-indigo-50 text-indigo-600 hover:bg-indigo-100 font-medium py-2.5 px-6 rounded-xl transition-colors">Terapkan</button>
                @if(request()->has('filter_type'))
                    <a href="{{ route('canteen-transactions.index') }}" class="bg-gray-100 text-gray-600 hover:bg-gray-200 font-medium py-2.5 px-4 rounded-xl transition-colors">Reset</a>
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
                        <th class="pb-3 font-medium px-4">Tanggal Transaksi</th>
                        <th class="pb-3 font-medium px-4 text-center">Jumlah Tap</th>
                        <th class="pb-3 font-medium px-4 text-right">Total Transaksi</th>
                        <th class="pb-3 font-medium px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($transactions as $trx)
                    <tr class="border-b border-gray-50 last:border-0 hover:bg-gray-50 transition-colors">
                        <td class="py-4 px-4 text-gray-800">
                            <div class="font-medium">{{ $trx->employee->name ?? 'Unknown' }}</div>
                            <div class="text-xs text-gray-500">{{ $trx->employee->nik ?? '-' }}</div>
                        </td>
                        <td class="py-4 px-4 text-gray-600 tooltip">{{ \Carbon\Carbon::parse($trx->transaction_date)->format('d M Y') }}</td>
                        <td class="py-4 px-4 text-center text-gray-600 font-semibold">{{ $trx->tap_count }}x</td>
                        <td class="py-4 px-4 text-right text-gray-800 font-bold">
                            Rp {{ number_format($trx->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="py-4 px-4 text-center">
                            <div class="flex justify-center space-x-2">
                                <a href="{{ route('canteen-transactions.edit', $trx->id) }}" class="p-2 text-indigo-500 hover:bg-indigo-50 rounded-lg transition-colors" title="Edit">
                                    <i class="ph ph-pencil-simple text-lg"></i>
                                </a>
                                <form action="{{ route('canteen-transactions.destroy', $trx->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
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
                        <td colspan="5" class="py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <i class="ph ph-receipt text-4xl mb-3 text-gray-300"></i>
                                Belum ada data transaksi kantin.
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($transactions->count() > 0)
                <tfoot>
                    <tr class="border-t-2 border-gray-100">
                        <td colspan="3" class="py-4 px-4 text-right font-bold text-gray-700">TOTAL HASIL FILTER:</td>
                        <td class="py-4 px-4 text-right font-black text-indigo-600 text-lg">Rp {{ number_format($totalAmountSum, 0, ',', '.') }}</td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
        
        <div class="mt-6">
            {{ $transactions->links() }}
        </div>
    </div>

    <script>
        function updateFilterInputs() {
            // Sembunyikan semua
            document.querySelectorAll('.filter-input').forEach(el => el.style.display = 'none');
            
            // Tampilkan yang dipilih
            const val = document.getElementById('filter_type').value;
            if(val) {
                const target = document.getElementById('input_' + val);
                if(target) target.style.display = 'block';
            }
        }
        
        // Panggil saat load untuk mempertahankan state filter saat ini
        document.addEventListener('DOMContentLoaded', updateFilterInputs);
    </script>
</x-app-layout>
