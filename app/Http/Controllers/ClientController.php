<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Store;
use App\Models\Client;

class ClientController extends Controller
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
        $store   = $this->currentStore();
        $clients = $store->clients()->orderByDesc('total_orders')->get();
        return view('store.clients.index', compact('store', 'clients'));
    }

    public function getStoresApi(Request $request)
    {
        $stores = Store::where('status', 'active')
            ->select('id', 'store_name', 'owner_name', 'address', 'phone', 'latitude', 'longitude')
            ->get();

        if ($request->filled('lat') && $request->filled('lng')) {
            $lat = (float) $request->lat;
            $lng = (float) $request->lng;

            $stores = $stores->map(function ($store) use ($lat, $lng) {
                $store->distance = ($store->latitude && $store->longitude)
                    ? $this->haversineKm($lat, $lng, $store->latitude, $store->longitude)
                    : null;
                return $store;
            })->sortBy(fn($s) => $s->distance ?? PHP_INT_MAX)->values();
        }

        return response()->json($stores);
    }

    public function getStockApi(Request $request)
    {
        $store = Store::find($request->store_id);
        if (!$store) return response()->json([]);
        return response()->json($store->stock()->where('quantity', '>', 0)->get());
    }

    public function stockAlertsApi()
    {
        $store = Auth::guard('store')->user() ?? Auth::guard('staff')->user()?->store;
        if (!$store) return response()->json(['low_stock' => 0, 'pending_orders' => 0, 'latest_order_id' => 0]);

        $latestPending = $store->orders()->where('status', 'pending')->latest()->value('id') ?? 0;

        return response()->json([
            'low_stock'       => $store->stock()->whereRaw('quantity <= alert_threshold')->count(),
            'pending_orders'  => $store->orders()->where('status', 'pending')->count(),
            'latest_order_id' => $latestPending,
        ]);
    }

    private function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $R = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return round($R * 2 * atan2(sqrt($a), sqrt(1 - $a)), 1);
    }
}
