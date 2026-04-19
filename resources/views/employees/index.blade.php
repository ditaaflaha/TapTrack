<x-app-layout>
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Daftar Karyawan</h2>
            <p class="text-gray-500 text-sm mt-1">Kelola data seluruh karyawan perusahaan Anda.</p>
        </div>
        <a href="{{ route('employees.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-xl shadow-md transition-all flex items-center">
            <i class="ph ph-plus mr-2"></i> Tambah Karyawan
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-max">
                <thead>
                    <tr class="text-xs text-gray-400 border-b border-gray-100 uppercase tracking-wider">
                        <th class="pb-3 font-medium px-4">NIK</th>
                        <th class="pb-3 font-medium px-4">Nama</th>
                        <th class="pb-3 font-medium px-4">Departemen</th>
                        <th class="pb-3 font-medium px-4">Jabatan</th>
                        <th class="pb-3 font-medium px-4">Join Date</th>
                        <th class="pb-3 font-medium px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($employees as $employee)
                    <tr class="border-b border-gray-50 last:border-0 hover:bg-gray-50 transition-colors">
                        <td class="py-4 px-4 font-semibold text-gray-700">{{ $employee->nik }}</td>
                        <td class="py-4 px-4 text-gray-800">
                            <div class="font-medium">{{ $employee->name }}</div>
                            <div class="text-xs text-gray-500">{{ $employee->gender }} • {{ $employee->religion }}</div>
                        </td>
                        <td class="py-4 px-4 text-gray-600">
                            <span class="bg-blue-50 text-blue-600 px-3 py-1 rounded-full text-xs font-medium">{{ $employee->department }}</span>
                        </td>
                        <td class="py-4 px-4 text-gray-600">{{ $employee->position }}</td>
                        <td class="py-4 px-4 text-gray-600">{{ \Carbon\Carbon::parse($employee->join_date)->format('d M Y') }}</td>
                        <td class="py-4 px-4 text-center">
                            <div class="flex justify-center space-x-2">
                                <a href="{{ route('employees.edit', $employee->id) }}" class="p-2 text-indigo-500 hover:bg-indigo-50 rounded-lg transition-colors" title="Edit">
                                    <i class="ph ph-pencil-simple text-lg"></i>
                                </a>
                                <form action="{{ route('employees.destroy', $employee->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
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
                        <td colspan="6" class="py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <i class="ph ph-users text-4xl mb-3 text-gray-300"></i>
                                Belum ada data karyawan.
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-6">
            {{ $employees->links() }}
        </div>
    </div>
</x-app-layout>
