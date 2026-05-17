<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Store;
use App\Models\Sale;
use App\Models\Client;
use App\Models\Stock;
use Carbon\Carbon;

class SaleController extends Controller
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
        $query = $store->sales()->with('client')->latest('sale_date');

        if ($request->filled('date_from')) {
            $query->where('sale_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('sale_date', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $query->where('client_name', 'like', '%' . $request->search . '%');
        }

        $sales = $query->paginate(20);
        $total = $query->sum('amount');

        return view('store.sales.index', compact('store', 'sales', 'total'));
    }

    public function create()
    {
        $store = $this->currentStore();
        $stocks  = $store->stock()->where('quantity', '>', 0)->get();
        $clients = $store->clients()->get();
        return view('store.sales.create', compact('store', 'stocks', 'clients'));
    }

    public function store(Request $request)
    {
        $store = $this->currentStore();

        $request->validate([
            'client_name' => 'required|string|max:255',
            'brand'       => 'required|string',
            'weight'      => 'required|string',
            'quantity'    => 'required|integer|min:1',
            'unit_price'  => 'required|numeric|min:0',
            'currency'    => 'required|string|max:10',
            'sale_date'   => 'required|date',
        ]);

        $stock = $store->stock()->where('brand', $request->brand)->where('weight', $request->weight)->first();
        if (!$stock || $stock->quantity < $request->quantity) {
            return back()->with('error', 'Stock insuffisant.')->withInput();
        }

        $stock->decrement('quantity', $request->quantity);

        $client = null;
        if ($request->client_phone) {
            $client = $store->clients()->firstOrCreate(
                ['phone' => $request->client_phone],
                ['name' => $request->client_name]
            );
            $client->increment('total_orders');

            $loyalty = $store->loyaltyProgram;
            if ($loyalty && $loyalty->active) {
                $client->increment('loyalty_points', intval($request->quantity * $loyalty->points_per_unit));
            }
        }

        $store->sales()->create([
            'client_id'   => $client?->id,
            'client_name' => $request->client_name,
            'brand'       => $request->brand,
            'weight'      => $request->weight,
            'quantity'    => $request->quantity,
            'unit_price'  => $request->unit_price,
            'amount'      => $request->quantity * $request->unit_price,
            'currency'    => $request->currency,
            'sale_date'   => $request->sale_date,
            'description' => $request->description,
        ]);

        return redirect()->route('sales.index')->with('success', 'Vente enregistrée avec succès.');
    }
}
