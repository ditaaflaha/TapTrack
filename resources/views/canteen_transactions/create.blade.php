<x-app-layout>
    <div class="mb-6 flex items-center">
        <a href="{{ route('canteen-transactions.index') }}" class="mr-4 w-10 h-10 bg-white border border-gray-200 rounded-xl flex items-center justify-center text-gray-500 hover:bg-gray-50 hover:text-gray-800 transition-colors">
            <i class="ph ph-arrow-left text-lg"></i>
        </a>
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Tambah Transaksi Kantin</h2>
            <p class="text-gray-500 text-sm mt-1">Catat aktivitas tap kantin untuk karyawan spesifik.</p>
        </div>
    </div>

    <div class="bg-blue-50 border border-blue-200 text-blue-700 p-4 mb-6 rounded-2xl flex items-center max-w-4xl">
        <i class="ph-fill ph-info text-2xl mr-3 text-blue-500"></i>
        <div>
            <h4 class="font-bold">Informasi Tarif</h4>
            <p class="text-sm">Satu kali Tap dihargai sebesar <strong>Rp 12.000</strong>. Sistem akan otomatis menghitung totalnya, Anda hanya perlu memasukkan jumlah Tap.</p>
        </div>
    </div>

    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 max-w-4xl">
        <form action="{{ route('canteen-transactions.store') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 gap-6">
                <!-- Karyawan -->
                <div>
                    <label for="employee_id" class="block text-sm font-semibold text-gray-700 mb-2">Nama Karyawan <span class="text-red-500">*</span></label>
                    <select name="employee_id" id="employee_id" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-gray-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition-colors" required>
                        <option value="">Pilih Karyawan</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->nik }} - {{ $emp->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('employee_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Tanggal Transaksi -->
                <div>
                    <label for="transaction_date" class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Transaksi <span class="text-red-500">*</span></label>
                    <input type="date" name="transaction_date" id="transaction_date" value="{{ old('transaction_date', date('Y-m-d')) }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-gray-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition-colors" required>
                    @error('transaction_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Jumlah Tap -->
                <div>
                    <label for="tap_count" class="block text-sm font-semibold text-gray-700 mb-2">Jumlah Tap <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="number" min="1" step="1" name="tap_count" id="tap_count" value="{{ old('tap_count', 1) }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-gray-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition-colors" required>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                            <span class="text-gray-500 font-medium">Kali Tap</span>
                        </div>
                    </div>
                    @error('tap_count') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-8 flex justify-end space-x-3 border-t border-gray-100 pt-6">
                <a href="{{ route('canteen-transactions.index') }}" class="px-6 py-3 bg-white border border-gray-200 text-gray-600 font-medium rounded-xl hover:bg-gray-50 transition-colors">Batal</a>
                <button type="submit" class="px-6 py-3 bg-indigo-600 text-white font-medium rounded-xl shadow-md hover:bg-indigo-700 transition-colors flex items-center">
                    <i class="ph ph-floppy-disk mr-2 text-lg"></i> Simpan Transaksi
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
