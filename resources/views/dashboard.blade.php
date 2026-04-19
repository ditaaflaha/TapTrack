<x-app-layout>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 mt-2">
        <!-- Saldo Kantin Card -->
        <div class="relative bg-gradient-to-br from-blue-500 to-indigo-600 rounded-3xl p-6 text-white overflow-hidden shadow-lg shadow-indigo-200">
            <!-- Decorative shape -->
            <div class="absolute -bottom-4 -right-4 w-32 h-32 bg-white/10 rounded-2xl transform rotate-12 backdrop-blur-sm"></div>
            <div class="absolute -bottom-8 -right-8 w-32 h-32 bg-white/10 rounded-2xl transform rotate-12 backdrop-blur-sm"></div>
            
            <h3 class="text-indigo-100 font-medium text-sm mb-1 relative z-10">Saldo Kantin</h3>
            <div class="text-4xl font-bold mb-4 relative z-10">Rp 0</div>
            <div class="inline-block bg-white/20 backdrop-blur-md px-4 py-1.5 rounded-full text-xs font-semibold text-white relative z-10">
                Tarif Flat: Rp 12.000
            </div>
        </div>

        <!-- Rolling Door Card -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex flex-col justify-between relative overflow-hidden">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-gray-500 font-medium text-sm mb-1">Rolling Door</h3>
                    <div class="text-xl font-bold text-gray-800">Terkunci</div>
                </div>
                <div class="w-12 h-12 bg-red-100 text-red-500 rounded-xl flex items-center justify-center">
                    <i class="ph-fill ph-door text-2xl"></i>
                </div>
            </div>
            <button class="w-full bg-[#f8fbff] text-indigo-600 font-bold text-xs py-3 rounded-xl border border-indigo-50 hover:bg-indigo-50 transition-colors uppercase tracking-wider">
                Kontrol Akses
            </button>
        </div>

        <!-- Tap Terakhir Card -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex flex-col justify-between">
            <div>
                <h3 class="text-gray-500 font-medium text-sm mb-1">Tap Terakhir</h3>
                <div class="text-2xl font-bold text-gray-800 mb-4">08:00 AM</div>
            </div>
            <div class="flex items-center text-green-500 text-sm font-semibold">
                <i class="ph-fill ph-check-circle mr-2 text-lg"></i>
                Tepat Waktu
            </div>
        </div>
    </div>

    <!-- Riwayat Aktivitas RFID Table -->
    <div class="bg-white border text-gray-800 border-gray-100 shadow-sm rounded-3xl p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-bold text-gray-800">Riwayat Aktivitas RFID</h3>
            <button class="bg-indigo-50 text-indigo-600 px-4 py-1.5 rounded-full text-xs font-semibold hover:bg-indigo-100 transition-colors">
                Hari Ini
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-xs text-gray-400 border-b border-gray-100 uppercase tracking-wider">
                        <th class="pb-3 font-medium">Aktivitas</th>
                        <th class="pb-3 font-medium">Waktu</th>
                        <th class="pb-3 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <tr class="border-b border-gray-50 last:border-0 hover:bg-gray-50 transition-colors">
                        <td class="py-4 flex items-center text-gray-700 font-medium">
                            <div class="w-10 h-10 rounded-xl bg-orange-100 text-orange-500 flex items-center justify-center mr-4">
                                <i class="ph-fill ph-fork-knife text-lg"></i>
                            </div>
                            Transaksi Kantin
                        </td>
                        <td class="py-4 text-gray-600">12:15 PM</td>
                        <td class="py-4 font-semibold text-red-500">- Rp 12.000</td>
                    </tr>
                    <tr class="border-b border-gray-50 last:border-0 hover:bg-gray-50 transition-colors">
                        <td class="py-4 flex items-center text-gray-700 font-medium">
                            <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-500 flex items-center justify-center mr-4">
                                <i class="ph-fill ph-sign-in text-lg"></i>
                            </div>
                            Masuk Kantor
                        </td>
                        <td class="py-4 text-gray-600">07:58 AM</td>
                        <td class="py-4 font-semibold text-green-500">Berhasil</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
