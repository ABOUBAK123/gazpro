<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Store;
use App\Models\Order;
use App\Models\Sale;
use App\Models\Client;
use App\Models\Stock;
use App\Http\Controllers\LivreurController;
use Carbon\Carbon;

class OrderController extends Controller
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
        $query = $store->orders()->with('client')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where('client_name', 'like', '%' . $request->search . '%');
        }

        $orders   = $query->paginate(20);
        $livreurs = LivreurController::livreursSortedByDistance($store);
        return view('store.orders.index', compact('store', 'orders', 'livreurs'));
    }

    public function create()
    {
        $store = $this->currentStore();
        $stocks  = $store->stock()->where('quantity', '>', 0)->get();
        $clients = $store->clients()->get();
        return view('store.orders.create', compact('store', 'stocks', 'clients'));
    }

    public function store(Request $request)
    {
        $store = $this->currentStore();

        $request->validate([
            'client_name'  => 'required|string|max:255',
            'client_phone' => 'nullable|string|max:20',
            'brand'        => 'required|string',
            'weight'       => 'required|string',
            'quantity'     => 'required|integer|min:1',
            'unit_price'   => 'required|numeric|min:0',
            'currency'     => 'required|string|max:10',
        ]);

        $total = $request->quantity * $request->unit_price;

        $order = $store->orders()->create([
            'client_name'    => $request->client_name,
            'client_phone'   => $request->client_phone,
            'client_address' => $request->client_address,
            'brand'          => $request->brand,
            'weight'         => $request->weight,
            'quantity'       => $request->quantity,
            'unit_price'     => $request->unit_price,
            'total_price'    => $total,
            'currency'       => $request->currency,
            'notes'          => $request->notes,
            'status'         => 'pending',
        ]);

        return redirect()->route('orders.index')->with('success', 'Commande créée avec succès.');
    }

    public function updateStatus(Request $request, Order $order)
    {
        $store = $this->currentStore();
        abort_if($order->store_id !== $store->id, 403);

        $request->validate(['status' => 'required|in:pending,confirmed,delivered,cancelled']);

        $oldStatus = $order->status;
        $order->update(['status' => $request->status]);

        // When delivered: deduct stock and create sale
        if ($request->status === 'delivered' && $oldStatus !== 'delivered') {
            $stock = $store->stock()->where('brand', $order->brand)->where('weight', $order->weight)->first();
            if ($stock && $stock->quantity >= $order->quantity) {
                $stock->decrement('quantity', $order->quantity);
            }

            // Find or create client
            $client = null;
            if ($order->client_phone) {
                $client = $store->clients()->firstOrCreate(
                    ['phone' => $order->client_phone],
                    ['name' => $order->client_name]
                );
                $client->increment('total_orders');

                // Add loyalty points
                $loyalty = $store->loyaltyProgram;
                if ($loyalty && $loyalty->active) {
                    $points = intval($order->quantity * $loyalty->points_per_unit);
                    $client->increment('loyalty_points', $points);
                }
            }

            // Create sale
            $store->sales()->create([
                'client_id'   => $client?->id,
                'order_id'    => $order->id,
                'client_name' => $order->client_name,
                'brand'       => $order->brand,
                'weight'      => $order->weight,
                'quantity'    => $order->quantity,
                'unit_price'  => $order->unit_price,
                'amount'      => $order->total_price,
                'currency'    => $order->currency,
                'sale_date'   => now()->toDateString(),
            ]);
        }

        return back()->with('success', 'Statut de la commande mis à jour.');
    }

    public function destroy(Order $order)
    {
        $store = $this->currentStore();
        abort_if($order->store_id !== $store->id, 403);
        $order->delete();
        return back()->with('success', 'Commande supprimée.');
    }

    // Public client order form
    public function clientForm()
    {
        $stores = Store::where('status', 'active')->get();
        return view('client.order', compact('stores'));
    }

    public function clientStore(Request $request)
    {
        $request->validate([
            'store_id'     => 'required|exists:stores,id',
            'client_name'  => 'required|string|max:255',
            'client_phone' => 'required|string|max:20',
            'brand'        => 'required|string',
            'weight'       => 'required|string',
            'quantity'     => 'required|integer|min:1',
            'currency'     => 'required|string|max:10',
            'latitude'     => 'nullable|numeric|between:-90,90',
            'longitude'    => 'nullable|numeric|between:-180,180',
        ]);

        $store = Store::findOrFail($request->store_id);
        $stock = $store->stock()->where('brand', $request->brand)->where('weight', $request->weight)->first();

        if (!$stock || $stock->quantity < $request->quantity) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Stock insuffisant pour cette commande.'], 422);
            }
            return back()->with('error', 'Stock insuffisant pour cette commande.');
        }

        $order = $store->orders()->create([
            'client_name'    => $request->client_name,
            'client_phone'   => $request->client_phone,
            'client_address' => $request->client_address,
            'brand'          => $request->brand,
            'weight'         => $request->weight,
            'quantity'       => $request->quantity,
            'unit_price'     => $stock->unit_price,
            'total_price'    => $request->quantity * $stock->unit_price,
            'currency'       => $request->currency,
            'notes'          => $request->notes,
            'latitude'       => $request->latitude,
            'longitude'      => $request->longitude,
            'status'         => 'pending',
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'order_id' => $order->id]);
        }

        return redirect()->route('client.order')->with('success', 'Votre commande a été envoyée avec succès !');
    }
}
