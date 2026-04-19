<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendance::with('employee');

        // Filtering Logic
        if ($request->filled('filter_type')) {
            $type = $request->filter_type;

            if ($type === 'daily' && $request->filled('date')) {
                $query->whereDate('date', $request->date);
            } elseif ($type === 'weekly' && $request->filled('week_date')) {
                $endDate = Carbon::parse($request->week_date)->endOfDay();
                $startDate = $endDate->copy()->subDays(6)->startOfDay();
                $query->whereBetween('date', [$startDate, $endDate]);
            } elseif ($type === 'monthly' && $request->filled('month')) {
                $parts = explode('-', $request->month);
                if(count($parts) == 2){
                    $query->whereYear('date', $parts[0])
                          ->whereMonth('date', $parts[1]);
                }
            } elseif ($type === 'yearly' && $request->filled('year')) {
                $query->whereYear('date', $request->year);
            } elseif ($type === 'custom' && $request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('date', [$request->start_date, $request->end_date]);
            }
        } else {
            // Default show only this month to avoid heavy queries
            $query->whereMonth('date', Carbon::now()->month)
                  ->whereYear('date', Carbon::now()->year);
        }

        $attendances = $query->latest('date')->latest('id')->paginate(15);
        
        $totalLate = $query->where('status', 'Terlambat')->count();
        $totalOvertime = clone $query;
        $totalOvertimeSum = collect($query->get())->sum('overtime_hours');

        return view('attendances.index', compact('attendances', 'totalLate', 'totalOvertimeSum'));
    }

    public function create()
    {
        $employees = Employee::orderBy('name')->get();
        return view('attendances.create', compact('employees'));
    }

    private function computeAttendanceData(&$validated)
    {
        $validated['status'] = 'Tepat Waktu';
        $validated['overtime_hours'] = 0;

        if (!empty($validated['tap_in'])) {
            $tapInTime = Carbon::createFromFormat('H:i', $validated['tap_in']);
            $thresholdTime = Carbon::createFromFormat('H:i', '08:00');
            
            if ($tapInTime->greaterThan($thresholdTime)) {
                $validated['status'] = 'Terlambat';
            }
        }

        if (!empty($validated['tap_out'])) {
            $tapOutTime = Carbon::createFromFormat('H:i', $validated['tap_out']);
            $endDayTime = Carbon::createFromFormat('H:i', '17:00');
            
            $diffHours = $endDayTime->diffInHours($tapOutTime, false); 
            if ($diffHours >= 1) {
                // Berarti lebih dari atau sama dengan 1 jam lembur -> 18:00 ke atas
                $validated['overtime_hours'] = floor($diffHours);
            }
        }

        $validated['is_manual'] = request()->has('is_manual');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'tap_in' => 'nullable|date_format:H:i',
            'tap_out' => 'nullable|date_format:H:i',
        ]);

        $this->computeAttendanceData($validated);
        Attendance::create($validated);

        return redirect()->route('attendances.index')->with('success', 'Data absensi berhasil ditambahkan.');
    }

    public function show(Attendance $attendance)
    {
        return view('attendances.show', compact('attendance'));
    }

    public function edit(Attendance $attendance)
    {
        $employees = Employee::orderBy('name')->get();
        // format time for input value
        if($attendance->tap_in) {
            $attendance->tap_in = Carbon::parse($attendance->tap_in)->format('H:i');
        }
        if($attendance->tap_out) {
            $attendance->tap_out = Carbon::parse($attendance->tap_out)->format('H:i');
        }

        return view('attendances.edit', compact('attendance', 'employees'));
    }

    public function update(Request $request, Attendance $attendance)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'tap_in' => 'nullable|date_format:H:i',
            'tap_out' => 'nullable|date_format:H:i',
        ]);

        $this->computeAttendanceData($validated);
        $attendance->update($validated);

        return redirect()->route('attendances.index')->with('success', 'Data absensi berhasil diperbarui.');
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->delete();
        return redirect()->route('attendances.index')->with('success', 'Data absensi berhasil dihapus.');
    }
}
