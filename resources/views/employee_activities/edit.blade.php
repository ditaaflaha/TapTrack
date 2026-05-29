<x-app-layout>
    <div class="mb-6 flex items-center">
        <a href="{{ route('employee-activities.index') }}" class="mr-4 w-10 h-10 bg-white border border-gray-200 rounded-xl flex items-center justify-center text-gray-500 hover:bg-gray-50 hover:text-gray-800 transition-colors">
            <i class="ph ph-arrow-left text-lg"></i>
        </a>
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Edit Aktivitas Rolling Door</h2>
            <p class="text-gray-500 text-sm mt-1">Ubah catatan atau parameter dari aktivitas pemindaian rolling door karyawan.</p>
        </div>
    </div>

    <!-- warning banner -->
    <div class="bg-indigo-50 border border-indigo-200 text-indigo-800 p-4 mb-6 rounded-2xl flex items-start max-w-4xl shadow-sm">
        <i class="ph-fill ph-info text-2xl mr-3 text-indigo-500 mt-0.5"></i>
        <div>
            <h4 class="font-bold">Informasi Pengubahan</h4>
            <p class="text-sm">Menyimpan perubahan ini akan memperbarui data log aktivitas. Aktivitas yang diperbarui manual akan ditandai dengan tipe status **Manual (Admin)** secara otomatis.</p>
        </div>
    </div>

    <!-- Form Panel -->
    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 max-w-4xl">
        <form action="{{ route('employee-activities.update', $employeeActivity->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Karyawan -->
                <div class="md:col-span-2">
                    <label for="employee_id" class="block text-sm font-semibold text-gray-700 mb-2">Pilih Karyawan <span class="text-red-500">*</span></label>
                    <select name="employee_id" id="employee_id" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition-all" required>
                        <option value="">-- Cari & Pilih Karyawan --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ old('employee_id', $employeeActivity->employee_id) == $emp->id ? 'selected' : '' }}>
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
                            <input type="radio" name="activity_type" value="tap_in" class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 mr-3" {{ old('activity_type', $employeeActivity->activity_type) === 'tap_in' ? 'checked' : '' }}>
                            <div>
                                <span class="block text-sm font-bold text-gray-800">Tap In (Masuk)</span>
                                <span class="block text-xs text-gray-500 mt-0.5">Karyawan memasuki wilayah kantor / area kerja.</span>
                            </div>
                        </label>
                        
                        <!-- Tap Out -->
                        <label class="relative border border-gray-200 rounded-2xl p-4 flex items-center cursor-pointer hover:bg-gray-50 transition-all select-none">
                            <input type="radio" name="activity_type" value="tap_out" class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 mr-3" {{ old('activity_type', $employeeActivity->activity_type) === 'tap_out' ? 'checked' : '' }}>
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
                    <input type="date" name="scanned_date" id="scanned_date" value="{{ old('scanned_date', $employeeActivity->scanned_date) }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition-all" required>
                    @error('scanned_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Waktu -->
                <div>
                    <label for="scanned_time" class="block text-sm font-semibold text-gray-700 mb-2">Waktu / Jam Aktivitas (HH:MM) <span class="text-red-500">*</span></label>
                    <input type="time" name="scanned_time" id="scanned_time" value="{{ old('scanned_time', $employeeActivity->scanned_time) }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition-all" required>
                    @error('scanned_time') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Catatan / Keterangan -->
                <div class="md:col-span-2">
                    <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">Catatan / Keterangan</label>
                    <textarea name="notes" id="notes" rows="3" placeholder="Sebutkan alasan mengapa diinput manual (cth: Lupa bawa kartu RFID, Ketinggalan, dsb)..." class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition-all">{{ old('notes', $employeeActivity->notes) }}</textarea>
                    @error('notes') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-8 flex justify-end space-x-3 border-t border-gray-100 pt-6">
                <a href="{{ route('employee-activities.index') }}" class="px-6 py-3 bg-white border border-gray-200 text-gray-600 font-medium rounded-xl hover:bg-gray-50 transition-colors">Batal</a>
                <button type="submit" class="px-6 py-3 bg-indigo-600 text-white font-medium rounded-xl shadow-md hover:bg-indigo-700 transition-all duration-300 flex items-center">
                    <i class="ph ph-floppy-disk mr-2 text-lg"></i> Perbarui Aktivitas
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
