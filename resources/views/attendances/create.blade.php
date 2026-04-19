<x-app-layout>
    <div class="mb-6 flex items-center">
        <a href="{{ route('attendances.index') }}" class="mr-4 w-10 h-10 bg-white border border-gray-200 rounded-xl flex items-center justify-center text-gray-500 hover:bg-gray-50 hover:text-gray-800 transition-colors">
            <i class="ph ph-arrow-left text-lg"></i>
        </a>
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Rekam Absensi Manual</h2>
            <p class="text-gray-500 text-sm mt-1">Masukkan data Tap In/Out secara manual jika terjadi kendala mesin.</p>
        </div>
    </div>

    <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 p-4 mb-6 rounded-2xl flex items-center max-w-4xl">
        <i class="ph-fill ph-warning text-2xl mr-3 text-yellow-500"></i>
        <div>
            <h4 class="font-bold">Ketentuan Kedisiplinan</h4>
            <p class="text-sm">Batas masuk (*Tap In*) adalah <strong>08:00 AM</strong>. Lebih dari itu, sistem menghitung Terlambat. Waktu standar pulang (*Tap Out*) adalah <strong>17:00</strong>. Jika lewat jam 18:00, terhitung Lembur.</p>
        </div>
    </div>

    <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 max-w-4xl">
        <form action="{{ route('attendances.store') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Karyawan -->
                <div class="md:col-span-2">
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

                <!-- Tanggal -->
                <div class="md:col-span-2">
                    <label for="date" class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Absensi <span class="text-red-500">*</span></label>
                    <input type="date" name="date" id="date" value="{{ old('date', date('Y-m-d')) }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-gray-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition-colors" required>
                    @error('date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Tap In -->
                <div>
                    <label for="tap_in" class="block text-sm font-semibold text-gray-700 mb-2">Waktu Tap In (HH:MM)</label>
                    <input type="time" name="tap_in" id="tap_in" value="{{ old('tap_in') }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-gray-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition-colors">
                    @error('tap_in') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Tap Out -->
                <div>
                    <label for="tap_out" class="block text-sm font-semibold text-gray-700 mb-2">Waktu Tap Out (HH:MM)</label>
                    <input type="time" name="tap_out" id="tap_out" value="{{ old('tap_out') }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-gray-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white transition-colors">
                    @error('tap_out') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                
                <div class="md:col-span-2 pt-4">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="is_manual" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 w-5 h-5 mr-3" checked>
                        <span class="text-sm font-medium text-gray-700 hover:text-indigo-600 transition-colors">Tandai bahwa Absensi ini direkam Manual (Contoh: Mesin Error)</span>
                    </label>
                </div>
            </div>

            <div class="mt-8 flex justify-end space-x-3 border-t border-gray-100 pt-6">
                <a href="{{ route('attendances.index') }}" class="px-6 py-3 bg-white border border-gray-200 text-gray-600 font-medium rounded-xl hover:bg-gray-50 transition-colors">Batal</a>
                <button type="submit" class="px-6 py-3 bg-indigo-600 text-white font-medium rounded-xl shadow-md hover:bg-indigo-700 transition-colors flex items-center">
                    <i class="ph ph-floppy-disk mr-2 text-lg"></i> Simpan Data Absensi
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
