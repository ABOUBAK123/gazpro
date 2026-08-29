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

    public function index(Request $request)
    {
        $store = $this->currentStore();

        $query = $store->stock()->orderBy('brand')->orderBy('weight');

        if ($request->filled('date_debut')) {
            $query->whereDate('restocked_at', '>=', $request->date_debut);
        }
        if ($request->filled('date_fin')) {
            $query->whereDate('restocked_at', '<=', $request->date_fin);
        }

        $stocks = $query->get();

        // Same defaults as admin/settings.blade.php's "Marques & Poids" tab,
        // so the dropdown isn't empty before an admin has explicitly saved
        // a custom list there.
        $brands = collect(AppSetting::get('brands', ['Total', 'Shell', 'Oryx', 'Sodigaz', 'Petrogaz']))
            ->map(fn($b) => is_string($b) ? $b : ($b['name'] ?? null))
            ->filter()
            ->values();

        $weights = collect(AppSetting::get('weights', [
                ['value' => '6kg',  'code' => 'B6'],
                ['value' => '12kg', 'code' => 'B12'],
                ['value' => '25kg', 'code' => 'B25'],
            ]))
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
            $existing->update([
                'unit_price'       => $request->unit_price,
                'alert_threshold'  => $request->alert_threshold,
                // "Stock initial" = quantity resulting from this replenishment,
                // stays fixed until the next restock even as quantity depletes.
                'initial_quantity' => $existing->quantity,
                'restocked_at'     => now(),
            ]);
            return back()->with('success', 'Stock mis à jour avec succès.');
        }

        $store->stock()->create($request->only('brand', 'weight', 'quantity', 'unit_price', 'alert_threshold') + [
            'initial_quantity' => $request->quantity,
            'restocked_at'     => now(),
        ]);
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
