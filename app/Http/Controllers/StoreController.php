<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Store;
use App\Models\Stock;
use App\Models\Order;
use App\Models\Sale;
use App\Models\Expense;
use App\Models\Client;
use Carbon\Carbon;

class StoreController extends Controller
{
    private function currentStore(): Store
    {
        if (Auth::guard('store')->check()) {
            return Auth::guard('store')->user();
        }
        return Auth::guard('staff')->user()->store;
    }

    private function periodDates(string $period, ?string $from = null, ?string $to = null): array
    {
        return match ($period) {
            'week'  => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'year'  => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()],
            'custom' => [
                $from ? Carbon::parse($from)->startOfDay() : Carbon::now()->startOfMonth(),
                $to   ? Carbon::parse($to)->endOfDay()     : Carbon::now()->endOfDay(),
            ],
            default => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
        };
    }

    public function dashboard(Request $request)
    {
        $store = $this->currentStore();
        $today = Carbon::today();
        $month = Carbon::now()->startOfMonth();

        $period   = $request->input('period', 'month');
        $fromDate = $request->input('from');
        $toDate   = $request->input('to');
        [$periodStart, $periodEnd] = $this->periodDates($period, $fromDate, $toDate);

        $stats = [
            'total_stock'    => $store->stock()->sum('quantity'),
            'stock_items'    => $store->stock()->count(),
            'low_stock'      => $store->stock()->whereRaw('quantity <= alert_threshold')->count(),
            'pending_orders' => $store->orders()->where('status', 'pending')->count(),
            'today_sales'    => $store->sales()->whereDate('sale_date', $today)->sum('amount'),
            'month_sales'    => $store->sales()->where('sale_date', '>=', $month)->sum('amount'),
            'total_clients'  => $store->clients()->count(),
            'month_expenses' => $store->expenses()->where('expense_date', '>=', $month)->sum('amount'),
            // Period-filtered financials
            'period_revenue'  => $store->sales()->whereBetween('sale_date', [$periodStart, $periodEnd])->sum('amount'),
            'period_expenses' => $store->expenses()->whereBetween('expense_date', [$periodStart, $periodEnd])->sum('amount'),
        ];
        $stats['period_profit'] = $stats['period_revenue'] - $stats['period_expenses'];

        $recent_orders = $store->orders()->with('client')->latest()->take(5)->get();
        $low_stock     = $store->stock()->whereRaw('quantity <= alert_threshold')->get();

        return view('store.dashboard', compact('store', 'stats', 'recent_orders', 'low_stock', 'period', 'fromDate', 'toDate', 'periodStart', 'periodEnd'));
    }

    public function dashboardData(Request $request)
    {
        $store = $this->currentStore();
        $period   = $request->input('period', 'month');
        $fromDate = $request->input('from');
        $toDate   = $request->input('to');
        [$periodStart, $periodEnd] = $this->periodDates($period, $fromDate, $toDate);

        $revenue  = $store->sales()->whereBetween('sale_date', [$periodStart, $periodEnd])->sum('amount');
        $expenses = $store->expenses()->whereBetween('expense_date', [$periodStart, $periodEnd])->sum('amount');

        return response()->json([
            'revenue'  => (float) $revenue,
            'expenses' => (float) $expenses,
            'profit'   => (float) ($revenue - $expenses),
        ]);
    }
}
