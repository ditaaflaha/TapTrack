<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::latest()->paginate(10);
        return view('employees.index', compact('employees'));
    }

    public function create()
    {
        return view('employees.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nik' => 'required|string|unique:employees,nik',
            'name' => 'required|string|max:255',
            'gender' => 'required|string',
            'religion' => 'required|string',
            'join_date' => 'required|date',
            'department' => 'required|string',
            'position' => 'required|string',
            'date_of_birth' => 'required|date',
        ]);

        Employee::create($validated);

        return redirect()->route('employees.index')->with('success', 'Data Karyawan berhasil ditambahkan.');
    }

    public function show(Employee $employee)
    {
        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        return view('employees.edit', compact('employee'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'nik' => 'required|string|unique:employees,nik,' . $employee->id,
            'name' => 'required|string|max:255',
            'gender' => 'required|string',
            'religion' => 'required|string',
            'join_date' => 'required|date',
            'department' => 'required|string',
            'position' => 'required|string',
            'date_of_birth' => 'required|date',
        ]);

        $employee->update($validated);

        return redirect()->route('employees.index')->with('success', 'Data Karyawan berhasil diperbarui.');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        return redirect()->route('employees.index')->with('success', 'Data Karyawan berhasil dihapus.');
    }
}
