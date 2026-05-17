<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Store;
use App\Models\Salary;

class SalaryController extends Controller
{
    private function currentStore(): Store
    {
        return Auth::guard('store')->user();
    }

    public function index(Request $request)
    {
        $store = $this->currentStore();
        $query = $store->salaries()->with('staff')->latest();

        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        $salaries = $query->get();
        $staff    = $store->staff()->where('status', 'active')->get();

        return view('store.salaries.index', compact('store', 'salaries', 'staff'));
    }

    public function store(Request $request)
    {
        $store = $this->currentStore();

        $request->validate([
            'employee_name' => 'required|string|max:255',
            'base_amount'   => 'required|numeric|min:0',
            'bonus'         => 'nullable|numeric|min:0',
            'deductions'    => 'nullable|numeric|min:0',
            'currency'      => 'required|string|max:10',
            'month'         => 'required|string|regex:/^\d{4}-\d{2}$/',
        ]);

        $base       = $request->base_amount;
        $bonus      = $request->bonus ?? 0;
        $deductions = $request->deductions ?? 0;
        $total      = $base + $bonus - $deductions;

        $store->salaries()->create([
            'staff_id'      => $request->staff_id,
            'employee_name' => $request->employee_name,
            'base_amount'   => $base,
            'bonus'         => $bonus,
            'deductions'    => $deductions,
            'total_amount'  => $total,
            'currency'      => $request->currency,
            'month'         => $request->month,
            'status'        => 'pending',
        ]);

        return back()->with('success', 'Salaire enregistré.');
    }

    public function markPaid(Salary $salary)
    {
        $store = $this->currentStore();
        abort_if($salary->store_id !== $store->id, 403);
        $salary->update(['status' => 'paid']);
        return back()->with('success', 'Salaire marqué comme payé.');
    }

    public function destroy(Salary $salary)
    {
        $store = $this->currentStore();
        abort_if($salary->store_id !== $store->id, 403);
        $salary->delete();
        return back()->with('success', 'Salaire supprimé.');
    }
}
