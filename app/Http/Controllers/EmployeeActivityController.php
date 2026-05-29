<?php

namespace App\Http\Controllers;

use App\Models\EmployeeActivity;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

class EmployeeActivityController extends Controller
{
    /**
     * Pastikan tabel database termigrasi sebelum data diakses.
     */
    private function ensureMigrated()
    {
        if (!Schema::hasTable('employee_activities')) {
            try {
                Artisan::call('migrate', ['--force' => true]);
            } catch (\Exception $e) {
                // Abaikan atau log error jika migrasi gagal
            }
        }
    }

    public function index(Request $request)
    {
        $this->ensureMigrated();

        $query = EmployeeActivity::with('employee');

        // Filter: Pencarian nama/NIK karyawan
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('employee', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        // Filter: Tipe Aktivitas (tap_in/tap_out)
        if ($request->filled('activity_type')) {
            $query->where('activity_type', $request->activity_type);
        }

        // Filter: Rentang Waktu / Tanggal
        if ($request->filled('filter_type')) {
            $type = $request->filter_type;

            if ($type === 'daily' && $request->filled('date')) {
                $query->whereDate('scanned_at', $request->date);
            } elseif ($type === 'weekly' && $request->filled('week_date')) {
                $endDate = Carbon::parse($request->week_date)->endOfDay();
                $startDate = $endDate->copy()->subDays(6)->startOfDay();
                $query->whereBetween('scanned_at', [$startDate, $endDate]);
            } elseif ($type === 'monthly' && $request->filled('month')) {
                $parts = explode('-', $request->month);
                if (count($parts) == 2) {
                    $query->whereYear('scanned_at', $parts[0])
                          ->whereMonth('scanned_at', $parts[1]);
                }
            } elseif ($type === 'yearly' && $request->filled('year')) {
                $query->whereYear('scanned_at', $request->year);
            } elseif ($type === 'custom' && $request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('scanned_at', [$request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59']);
            }
        } else {
            // Default: Tampilkan data bulan ini
            $query->whereMonth('scanned_at', Carbon::now()->month)
                  ->whereYear('scanned_at', Carbon::now()->year);
        }

        $activities = $query->latest('scanned_at')->latest('id')->paginate(10);

        // Statistik Hari Ini
        $totalTapInToday = EmployeeActivity::whereDate('scanned_at', Carbon::today())
            ->where('activity_type', 'tap_in')
            ->count();

        $totalTapOutToday = EmployeeActivity::whereDate('scanned_at', Carbon::today())
            ->where('activity_type', 'tap_out')
            ->count();

        // Estimasi Karyawan yang Masih Berada di Dalam Perusahaan
        // Mengambil aktivitas terakhir masing-masing karyawan, jika aktivitas terakhirnya 'tap_in' berarti di dalam
        $latestActivitiesSub = EmployeeActivity::select('employee_id', \DB::raw('MAX(scanned_at) as max_scanned_at'))
            ->groupBy('employee_id');

        $employeesInsideCount = EmployeeActivity::joinSub($latestActivitiesSub, 'latest_acts', function ($join) {
                $join->on('employee_activities.employee_id', '=', 'latest_acts.employee_id')
                     ->on('employee_activities.scanned_at', '=', 'latest_acts.max_scanned_at');
            })
            ->where('employee_activities.activity_type', 'tap_in')
            ->count();

        return view('employee_activities.index', compact(
            'activities',
            'totalTapInToday',
            'totalTapOutToday',
            'employeesInsideCount'
        ));
    }

    public function create()
    {
        $this->ensureMigrated();
        $employees = Employee::orderBy('name')->get();
        return view('employee_activities.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'activity_type' => 'required|in:tap_in,tap_out',
            'scanned_date' => 'required|date',
            'scanned_time' => 'required|date_format:H:i',
            'notes' => 'nullable|string|max:255',
        ]);

        $scanned_at = Carbon::parse($validated['scanned_date'] . ' ' . $validated['scanned_time']);

        EmployeeActivity::create([
            'employee_id' => $validated['employee_id'],
            'activity_type' => $validated['activity_type'],
            'scanned_at' => $scanned_at,
            'is_manual' => true,
            'notes' => $validated['notes'],
        ]);

        return redirect()->route('employee-activities.index')->with('success', 'Aktivitas tap rolling door berhasil direkam.');
    }

    public function edit($id)
    {
        $this->ensureMigrated();
        $employeeActivity = EmployeeActivity::findOrFail($id);
        $employees = Employee::orderBy('name')->get();

        $employeeActivity->scanned_date = Carbon::parse($employeeActivity->scanned_at)->format('Y-m-d');
        $employeeActivity->scanned_time = Carbon::parse($employeeActivity->scanned_at)->format('H:i');

        return view('employee_activities.edit', compact('employeeActivity', 'employees'));
    }

    public function update(Request $request, $id)
    {
        $employeeActivity = EmployeeActivity::findOrFail($id);

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'activity_type' => 'required|in:tap_in,tap_out',
            'scanned_date' => 'required|date',
            'scanned_time' => 'required|date_format:H:i',
            'notes' => 'nullable|string|max:255',
        ]);

        $scanned_at = Carbon::parse($validated['scanned_date'] . ' ' . $validated['scanned_time']);

        $employeeActivity->update([
            'employee_id' => $validated['employee_id'],
            'activity_type' => $validated['activity_type'],
            'scanned_at' => $scanned_at,
            'notes' => $validated['notes'],
            'is_manual' => true,
        ]);

        return redirect()->route('employee-activities.index')->with('success', 'Data aktivitas rolling door berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $employeeActivity = EmployeeActivity::findOrFail($id);
        $employeeActivity->delete();

        return redirect()->route('employee-activities.index')->with('success', 'Data aktivitas rolling door berhasil dihapus.');
    }
}
