<?php

namespace App\Http\Controllers;

use App\Models\CanteenTransaction;
use App\Models\Employee;
use Illuminate\Http\Request;

class CanteenTransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = CanteenTransaction::with('employee');

        // Filtering logic
        if ($request->filled('filter_type')) {
            $type = $request->filter_type;

            if ($type === 'daily' && $request->filled('date')) {
                $query->whereDate('transaction_date', $request->date);
            } elseif ($type === 'monthly' && $request->filled('month')) {
                // format expected: YYYY-MM
                $parts = explode('-', $request->month);
                if(count($parts) == 2){
                    $query->whereYear('transaction_date', $parts[0])
                          ->whereMonth('transaction_date', $parts[1]);
                }
            } elseif ($type === 'yearly' && $request->filled('year')) {
                $query->whereYear('transaction_date', $request->year);
            }
        }

        $transactions = $query->latest('transaction_date')->latest('id')->paginate(10);
        $totalAmountSum = $query->sum('total_amount'); // Sum for the current filtered view

        return view('canteen_transactions.index', compact('transactions', 'totalAmountSum'));
    }

    public function create()
    {
        $employees = Employee::orderBy('name')->get();
        return view('canteen_transactions.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'transaction_date' => 'required|date',
            'tap_count' => 'required|integer|min:1',
        ]);

        $validated['total_amount'] = $validated['tap_count'] * 12000;

        CanteenTransaction::create($validated);

        return redirect()->route('canteen-transactions.index')->with('success', 'Transaksi kantin berhasil ditambahkan.');
    }

    public function show(CanteenTransaction $canteenTransaction)
    {
        return view('canteen_transactions.show', compact('canteenTransaction'));
    }

    public function edit(CanteenTransaction $canteenTransaction)
    {
        $employees = Employee::orderBy('name')->get();
        return view('canteen_transactions.edit', compact('canteenTransaction', 'employees'));
    }

    public function update(Request $request, CanteenTransaction $canteenTransaction)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'transaction_date' => 'required|date',
            'tap_count' => 'required|integer|min:1',
        ]);

        $validated['total_amount'] = $validated['tap_count'] * 12000;

        $canteenTransaction->update($validated);

        return redirect()->route('canteen-transactions.index')->with('success', 'Transaksi kantin berhasil diperbarui.');
    }

    public function destroy(CanteenTransaction $canteenTransaction)
    {
        $canteenTransaction->delete();
        return redirect()->route('canteen-transactions.index')->with('success', 'Transaksi kantin berhasil dihapus.');
    }
}
