<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Store;
use Carbon\Carbon;

class ProfitController extends Controller
{
    private function currentStore(): Store
    {
        if (Auth::guard('store')->check()) {
            return Auth::guard('store')->user();
        }
        return Auth::guard('staff')->user()->store;
    }

    public function index()
    {
        $store = $this->currentStore();
        $months = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $start = $month->copy()->startOfMonth();
            $end   = $month->copy()->endOfMonth();

            $revenue  = $store->sales()->whereBetween('sale_date', [$start, $end])->sum('amount');
            $expenses = $store->expenses()->whereBetween('expense_date', [$start, $end])->sum('amount');

            $months[] = [
                'label'    => $month->translatedFormat('M Y'),
                'revenue'  => (float) $revenue,
                'expenses' => (float) $expenses,
                'profit'   => (float) ($revenue - $expenses),
            ];
        }

        $totals = [
            'revenue'  => array_sum(array_column($months, 'revenue')),
            'expenses' => array_sum(array_column($months, 'expenses')),
            'profit'   => array_sum(array_column($months, 'profit')),
        ];

        return view('store.profit', compact('months', 'totals'));
    }
}
