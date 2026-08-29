<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Store;
use App\Models\Stock;
use App\Models\AppSetting;

class StockController extends Controller
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
        $stocks = $store->stock()->orderBy('brand')->orderBy('weight')->get();

        $brands = collect(AppSetting::get('brands', []))
            ->map(fn($b) => is_string($b) ? $b : ($b['name'] ?? null))
            ->filter()
            ->values();

        $weights = collect(AppSetting::get('weights', []))
            ->map(fn($w) => is_string($w) ? $w : ($w['value'] ?? null))
            ->filter()
            ->values();

        return view('store.stock.index', compact('store', 'stocks', 'brands', 'weights'));
    }

    public function store(Request $request)
    {
        $store = $this->currentStore();

        $request->validate([
            'brand'           => 'required|string|max:100',
            'weight'          => 'required|string|max:50',
            'quantity'        => 'required|integer|min:0',
            'unit_price'      => 'required|numeric|min:0',
            'alert_threshold' => 'required|integer|min:0',
        ]);

        $existing = $store->stock()->where('brand', $request->brand)->where('weight', $request->weight)->first();

        if ($existing) {
            $existing->increment('quantity', $request->quantity);
            $existing->update(['unit_price' => $request->unit_price, 'alert_threshold' => $request->alert_threshold]);
            return back()->with('success', 'Stock mis à jour avec succès.');
        }

        $store->stock()->create($request->only('brand', 'weight', 'quantity', 'unit_price', 'alert_threshold'));
        return back()->with('success', 'Article ajouté au stock.');
    }

    public function update(Request $request, Stock $stock)
    {
        $store = $this->currentStore();
        abort_if($stock->store_id !== $store->id, 403);

        $request->validate([
            'quantity'        => 'required|integer|min:0',
            'unit_price'      => 'required|numeric|min:0',
            'alert_threshold' => 'required|integer|min:0',
        ]);

        $stock->update($request->only('quantity', 'unit_price', 'alert_threshold'));
        return back()->with('success', 'Stock mis à jour.');
    }

    public function destroy(Stock $stock)
    {
        $store = $this->currentStore();
        abort_if($stock->store_id !== $store->id, 403);
        $stock->delete();
        return back()->with('success', 'Article supprimé du stock.');
    }
}
