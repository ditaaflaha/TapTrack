<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\CanteenTransaction;
use App\Models\Attendance;
use App\Models\EmployeeActivity;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

class DashboardController extends Controller
{
    private function ensureMigrated()
    {
        if (!Schema::hasTable('employee_activities')) {
            try {
                Artisan::call('migrate', ['--force' => true]);
            } catch (\Exception $e) {
                // Abaikan jika migrasi gagal
            }
        }
    }

    private function seedMockData()
    {
        if (Employee::count() > 0) {
            return;
        }

        // Seed Employees
        $employees = [
            [
                'nik' => 'NIK102938',
                'name' => 'Budi Santoso',
                'gender' => 'Laki-laki',
                'religion' => 'Islam',
                'join_date' => '2023-01-15',
                'department' => 'IT Department',
                'position' => 'Senior Developer',
                'date_of_birth' => '1992-08-25',
            ],
            [
                'nik' => 'NIK203948',
                'name' => 'Siti Aminah',
                'gender' => 'Perempuan',
                'religion' => 'Islam',
                'join_date' => '2024-03-10',
                'department' => 'Finance & HR',
                'position' => 'HR Specialist',
                'date_of_birth' => '1995-11-12',
            ],
            [
                'nik' => 'NIK304958',
                'name' => 'Rian Hidayat',
                'gender' => 'Laki-laki',
                'religion' => 'Kristen',
                'join_date' => '2022-06-01',
                'department' => 'Operations',
                'position' => 'Operations Head',
                'date_of_birth' => '1989-04-03',
            ],
            [
                'nik' => 'NIK405968',
                'name' => 'Dewi Lestari',
                'gender' => 'Perempuan',
                'religion' => 'Hindu',
                'join_date' => '2025-02-20',
                'department' => 'Marketing',
                'position' => 'Graphic Designer',
                'date_of_birth' => '1998-07-30',
            ],
            [
                'nik' => 'NIK506978',
                'name' => 'Michael Chen',
                'gender' => 'Laki-laki',
                'religion' => 'Katolik',
                'join_date' => '2023-09-01',
                'department' => 'IT Department',
                'position' => 'Support Engineer',
                'date_of_birth' => '1994-05-18',
            ]
        ];

        $createdEmployees = [];
        foreach ($employees as $empData) {
            $createdEmployees[] = Employee::create($empData);
        }

        // Seed Attendances
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        foreach ($createdEmployees as $index => $emp) {
            // Attendance Today
            Attendance::create([
                'employee_id' => $emp->id,
                'date' => $today->format('Y-m-d'),
                'tap_in' => $index % 2 == 0 ? '07:45' : '08:15',
                'tap_out' => '17:05',
                'status' => $index % 2 == 0 ? 'Tepat Waktu' : 'Terlambat',
                'overtime_hours' => 0,
                'is_manual' => false,
            ]);
            
            // Attendance Yesterday
            Attendance::create([
                'employee_id' => $emp->id,
                'date' => $yesterday->format('Y-m-d'),
                'tap_in' => '07:55',
                'tap_out' => '18:15',
                'status' => 'Tepat Waktu',
                'overtime_hours' => 1,
                'is_manual' => false,
            ]);
        }

        // Seed Canteen Transactions
        foreach ($createdEmployees as $emp) {
            CanteenTransaction::create([
                'employee_id' => $emp->id,
                'transaction_date' => $today->format('Y-m-d'),
                'tap_count' => 1,
                'total_amount' => 12000,
            ]);
        }

        // Seed Employee Activities (Rolling door log)
        foreach ($createdEmployees as $index => $emp) {
            // Tap In
            EmployeeActivity::create([
                'employee_id' => $emp->id,
                'activity_type' => 'tap_in',
                'scanned_at' => Carbon::now()->subHours(8 - $index),
                'is_manual' => false,
                'notes' => null,
            ]);
            
            // Tap Out (except the last two, who are still inside)
            if ($index < 3) {
                EmployeeActivity::create([
                    'employee_id' => $emp->id,
                    'activity_type' => 'tap_out',
                    'scanned_at' => Carbon::now()->subHours(2 - $index),
                    'is_manual' => false,
                    'notes' => $index == 1 ? 'Istirahat makan siang' : null,
                ]);
            }
        }
    }

    public function index()
    {
        $this->ensureMigrated();
        
        try {
            $this->seedMockData();
        } catch (\Exception $e) {
            // Abaikan jika seeding gagal
        }

        $totalEmployees = Employee::count();

        // Canteen Transactions Today
        $totalCanteenToday = CanteenTransaction::whereDate('transaction_date', Carbon::today())->sum('total_amount');

        // Attendances Today
        $totalAttendanceToday = Attendance::whereDate('date', Carbon::today())->count();

        // Last Rolling Door activities (feed)
        $latestActivities = EmployeeActivity::with('employee')
            ->latest('scanned_at')
            ->limit(5)
            ->get();

        // Last canteen transactions (feed)
        $latestCanteen = CanteenTransaction::with('employee')
            ->latest('transaction_date')
            ->latest('id')
            ->limit(5)
            ->get();

        // Estimated employees inside
        $employeesInsideCount = 0;
        if (Schema::hasTable('employee_activities') && EmployeeActivity::exists()) {
            $latestActivitiesSub = EmployeeActivity::select('employee_id', \DB::raw('MAX(scanned_at) as max_scanned_at'))
                ->groupBy('employee_id');

            $employeesInsideCount = EmployeeActivity::joinSub($latestActivitiesSub, 'latest_acts', function ($join) {
                    $join->on('employee_activities.employee_id', '=', 'latest_acts.employee_id')
                         ->on('employee_activities.scanned_at', '=', 'latest_acts.max_scanned_at');
                })
                ->where('employee_activities.activity_type', 'tap_in')
                ->count();
        }

        return view('dashboard', compact(
            'totalEmployees',
            'totalCanteenToday',
            'totalAttendanceToday',
            'latestActivities',
            'latestCanteen',
            'employeesInsideCount'
        ));
    }
}
