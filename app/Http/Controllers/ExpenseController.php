<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Store;
use App\Models\Expense;

class ExpenseController extends Controller
{
    private function currentStore(): Store
    {
        return Auth::guard('store')->user();
    }

    public function index(Request $request)
    {
        $store = $this->currentStore();
        $query = $store->expenses()->latest('expense_date');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('date_from')) {
            $query->where('expense_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('expense_date', '<=', $request->date_to);
        }

        $expenses = $query->paginate(20);
        $total    = $query->sum('amount');

        return view('store.expenses.index', compact('store', 'expenses', 'total'));
    }

    public function store(Request $request)
    {
        $store = $this->currentStore();

        $request->validate([
            'type'         => 'required|in:electricity,water,rent,maintenance,salary,other',
            'amount'       => 'required|numeric|min:0',
            'currency'     => 'required|string|max:10',
            'expense_date' => 'required|date',
            'description'  => 'nullable|string|max:500',
        ]);

        $store->expenses()->create($request->only('type', 'amount', 'currency', 'expense_date', 'description'));
        return back()->with('success', 'Dépense enregistrée.');
    }

    public function destroy(Expense $expense)
    {
        $store = $this->currentStore();
        abort_if($expense->store_id !== $store->id, 403);
        $expense->delete();
        return back()->with('success', 'Dépense supprimée.');
    }
}
