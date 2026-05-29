<x-app-layout>
    <div class="mb-6 flex items-center">
        <a href="{{ route('employee-activities.index') }}" class="mr-4 w-10 h-10 bg-white border border-gray-200 rounded-xl flex items-center justify-center text-gray-500 hover:bg-gray-50 hover:text-gray-800 transition-colors">
            <i class="ph ph-arrow-left text-lg"></i>
        </a>
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Rekam Aktivitas Manual</h2>
            <p class="text-gray-500 text-sm mt-1">Masukkan data Tap In/Out rolling door secara manual jika terjadi kendala kartu RFID atau mesin pembaca.</p>
        </div>
    </div>

    <!-- Warning Info -->
    <div class="bg-amber-50 border border-amber-200 text-amber-800 p-4 mb-6 rounded-2xl flex items-start max-w-4xl shadow-sm">
        <i class="ph-fill ph-warning-circle text-2xl mr-3 text-amber-500 mt-0.5"></i>
        <div>
            <h4 class="font-bold">Pemberitahuan Rekam Manual</h4>
            <p class="text-sm">Merekam data secara manual akan menandai entri ini sebagai tipe **Manual (Admin)**. Pastikan mencantumkan alasan pada kolom catatan untuk transparansi audit.</p>
        </div>
    </div>

    <!-- Form Panel -->
    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 max-w-4xl">
        <form action="{{ route('employee-activities.store') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Karyawan -->
                <div class="md:col-span-2">
                    <label for="employee_id" class="block text-sm font-semibold text-gray-700 mb-2">Pilih Karyawan <span class="text-red-500">*</span></label>
                    <select name="employee_id" id="employee_id" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition-all" required>
                        <option value="">-- Cari & Pilih Karyawan --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->nik }} - {{ $emp->name }} ({{ $emp->department }})
                            </option>
                        @endforeach
                    </select>
                    @error('employee_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Tipe Aktivitas -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Tipe Aktivitas <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Tap In -->
                        <label class="relative border border-gray-200 rounded-2xl p-4 flex items-center cursor-pointer hover:bg-gray-50 transition-all select-none">
                            <input type="radio" name="activity_type" value="tap_in" class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 mr-3" {{ old('activity_type', 'tap_in') === 'tap_in' ? 'checked' : '' }}>
                            <div>
                                <span class="block text-sm font-bold text-gray-800">Tap In (Masuk)</span>
                                <span class="block text-xs text-gray-500 mt-0.5">Karyawan memasuki wilayah kantor / area kerja.</span>
                            </div>
                        </label>
                        
                        <!-- Tap Out -->
                        <label class="relative border border-gray-200 rounded-2xl p-4 flex items-center cursor-pointer hover:bg-gray-50 transition-all select-none">
                            <input type="radio" name="activity_type" value="tap_out" class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 mr-3" {{ old('activity_type') === 'tap_out' ? 'checked' : '' }}>
                            <div>
                                <span class="block text-sm font-bold text-gray-800">Tap Out (Keluar)</span>
                                <span class="block text-xs text-gray-500 mt-0.5">Karyawan keluar dari wilayah kantor / area kerja.</span>
                            </div>
                        </label>
                    </div>
                    @error('activity_type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Tanggal -->
                <div>
                    <label for="scanned_date" class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Aktivitas <span class="text-red-500">*</span></label>
                    <input type="date" name="scanned_date" id="scanned_date" value="{{ old('scanned_date', date('Y-m-d')) }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition-all" required>
                    @error('scanned_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Waktu -->
                <div>
                    <label for="scanned_time" class="block text-sm font-semibold text-gray-700 mb-2">Waktu / Jam Aktivitas (HH:MM) <span class="text-red-500">*</span></label>
                    <input type="time" name="scanned_time" id="scanned_time" value="{{ old('scanned_time', date('H:i')) }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition-all" required>
                    @error('scanned_time') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Catatan / Keterangan -->
                <div class="md:col-span-2">
                    <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">Catatan / Keterangan</label>
                    <textarea name="notes" id="notes" rows="3" placeholder="Contoh: Lupa membawa kartu RFID, Istirahat makan siang, Dinas luar kota..." class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition-all">{{ old('notes') }}</textarea>
                    @error('notes') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-8 flex justify-end space-x-3 border-t border-gray-100 pt-6">
                <a href="{{ route('employee-activities.index') }}" class="px-6 py-3 bg-white border border-gray-200 text-gray-600 font-medium rounded-xl hover:bg-gray-50 transition-colors">Batal</a>
                <button type="submit" class="px-6 py-3 bg-indigo-600 text-white font-medium rounded-xl shadow-md hover:bg-indigo-700 transition-all duration-300 flex items-center">
                    <i class="ph ph-floppy-disk mr-2 text-lg"></i> Simpan Aktivitas
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
