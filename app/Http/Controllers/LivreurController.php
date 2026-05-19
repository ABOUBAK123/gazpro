<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Store;
use App\Models\Livreur;
use App\Models\Order;

class LivreurController extends Controller
{
    private function currentStore(): Store
    {
        if (Auth::guard('store')->check()) {
            return Auth::guard('store')->user();
        }
        return Auth::guard('staff')->user()->store;
    }

    private function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R   = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a   = sin($dLat / 2) ** 2
             + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    // Returns active+available livreurs sorted by distance to the given store
    public static function livreursSortedByDistance(Store $store): \Illuminate\Support\Collection
    {
        $livreurs = Livreur::where('status', 'active')
            ->withCount([
                'orders as active_count' => fn($q) => $q->whereIn('status', ['confirmed', 'en_route']),
            ])
            ->get();

        $storeLat = $store->latitude;
        $storeLng = $store->longitude;

        return $livreurs->map(function (Livreur $l) use ($storeLat, $storeLng) {
            if ($storeLat && $storeLng && $l->latitude && $l->longitude) {
                $R   = 6371;
                $dLat = deg2rad($l->latitude - $storeLat);
                $dLon = deg2rad($l->longitude - $storeLng);
                $a   = sin($dLat / 2) ** 2
                     + cos(deg2rad($storeLat)) * cos(deg2rad($l->latitude)) * sin($dLon / 2) ** 2;
                $l->distance_km = round($R * 2 * atan2(sqrt($a), sqrt(1 - $a)), 1);
            } else {
                $l->distance_km = null;
            }
            return $l;
        })->sortBy(function (Livreur $l) {
            // Available + with GPS first, then available without GPS, then busy
            $available = $l->is_available ? 0 : 1;
            $hasGps    = $l->distance_km !== null ? 0 : 1;
            return [$available, $hasGps, $l->distance_km ?? PHP_INT_MAX];
        })->values();
    }

    // ── Order assignment ───────────────────────────────────────────────────

    public function assignToOrder(Request $request, Order $order)
    {
        $store = $this->currentStore();
        abort_if($order->store_id !== $store->id, 403);

        $request->validate(['livreur_id' => 'required|exists:livreurs,id']);

        $livreur = Livreur::where('status', 'active')->findOrFail($request->livreur_id);

        $order->update([
            'livreur_id' => $livreur->id,
            'status'     => 'confirmed',
        ]);

        // Mark livreur as unavailable
        $livreur->update(['is_available' => false]);

        return back()->with('success', "Commande assignée à {$livreur->name}.");
    }

    public function unassign(Order $order)
    {
        abort_if($order->store_id !== $this->currentStore()->id, 403);

        if ($order->livreur) {
            $order->livreur->update(['is_available' => true]);
        }

        $order->update(['livreur_id' => null, 'status' => 'pending']);
        return back()->with('success', 'Livreur retiré de la commande.');
    }

    // ── Flutter JSON API ───────────────────────────────────────────────────

    public function apiData(string $token)
    {
        $livreur = Livreur::where('access_token', $token)->firstOrFail();

        $active = $livreur->orders()
            ->with('store')
            ->whereIn('status', ['confirmed', 'en_route'])
            ->latest()
            ->get()
            ->map(fn($o) => $this->formatOrderJson($o));

        $history = $livreur->orders()
            ->with('store')
            ->whereIn('status', ['delivered', 'cancelled'])
            ->latest()
            ->limit(30)
            ->get()
            ->map(fn($o) => $this->formatOrderJson($o));

        $deliveryFee    = (float) \App\Models\AppSetting::get('delivery_fee', 0);
        $totalEarnings  = $livreur->orders()
            ->where('status', 'delivered')
            ->count() * $deliveryFee;

        return response()->json([
            'livreur' => [
                'id'           => $livreur->id,
                'name'         => $livreur->name,
                'phone'        => $livreur->phone,
                'is_available' => (bool) $livreur->is_available,
            ],
            'active'       => $active,
            'history'      => $history,
            'deliveryFee'  => $deliveryFee,
            'totalEarnings'=> $totalEarnings,
        ]);
    }

    public function apiUpdatePosition(Request $request, string $token)
    {
        $livreur = Livreur::where('access_token', $token)->firstOrFail();
        $request->validate([
            'latitude'     => 'required|numeric|between:-90,90',
            'longitude'    => 'required|numeric|between:-180,180',
            'is_available' => 'sometimes|boolean',
        ]);

        $data = ['latitude' => $request->latitude, 'longitude' => $request->longitude];
        if ($request->has('is_available')) {
            $data['is_available'] = $request->boolean('is_available');
        }
        $livreur->update($data);

        return response()->json(['success' => true, 'is_available' => (bool) $livreur->fresh()->is_available]);
    }

    public function apiUpdateStatus(Request $request, string $token, Order $order)
    {
        $livreur = Livreur::where('access_token', $token)->firstOrFail();
        abort_if($order->livreur_id !== $livreur->id, 403);

        $request->validate(['status' => 'required|in:en_route,delivered,cancelled']);

        $oldStatus = $order->status;
        $order->update(['status' => $request->status]);

        if ($request->status === 'delivered' && $oldStatus !== 'delivered') {
            $livreur->update(['is_available' => true]);

            $store = $order->store;
            $stock = $store?->stock()
                ->where('brand', $order->brand)
                ->where('weight', $order->weight)
                ->first();

            if ($stock && $stock->quantity >= $order->quantity) {
                $stock->decrement('quantity', $order->quantity);
            }

            $client = null;
            if ($order->client_phone && $store) {
                $client = $store->clients()->firstOrCreate(
                    ['phone' => $order->client_phone],
                    ['name'  => $order->client_name]
                );
                $client->increment('total_orders');

                $loyalty = $store->loyaltyProgram;
                if ($loyalty && $loyalty->active) {
                    $client->increment('loyalty_points', intval($order->quantity * $loyalty->points_per_unit));
                }
            }

            if ($store) {
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
        }

        if ($request->status === 'cancelled') {
            $livreur->update(['is_available' => true]);
        }

        return response()->json(['success' => true, 'order' => $this->formatOrderJson($order->fresh())]);
    }

    private function formatOrderJson(Order $order): array
    {
        return [
            'id'              => $order->id,
            'storeName'       => $order->store?->store_name ?? '',
            'clientName'      => $order->client_name,
            'clientPhone'     => $order->client_phone,
            'clientAddress'   => $order->client_address,
            'brand'           => $order->brand,
            'weight'          => (string) $order->weight,
            'quantity'        => $order->quantity,
            'totalPrice'      => (float) $order->total_price,
            'currency'        => $order->currency ?? 'XOF',
            'status'          => $order->status,
            'notes'           => $order->notes,
            'createdAt'       => $order->created_at?->toISOString(),
        ];
    }

    // ── Livreur mobile app (public, token-based) ───────────────────────────

    public function mobileApp(string $token)
    {
        $livreur = Livreur::where('access_token', $token)->firstOrFail();

        $active = $livreur->orders()
            ->with('store')
            ->whereIn('status', ['confirmed', 'en_route'])
            ->latest()
            ->get();

        $history = $livreur->orders()
            ->whereIn('status', ['delivered', 'cancelled'])
            ->latest()
            ->limit(20)
            ->get();

        return view('livreur.app', compact('livreur', 'active', 'history', 'token'));
    }

    public function manifest(string $token)
    {
        $livreur  = Livreur::where('access_token', $token)->firstOrFail();
        $startUrl = route('livreur.app', $token);
        $iconBase = url('icons');

        return response()->json([
            'name'             => 'Livreur — ' . $livreur->name,
            'short_name'       => $livreur->name,
            'description'      => 'Application livreur GazManager',
            'start_url'        => $startUrl,
            'scope'            => url('/livreur/' . $token),
            'display'          => 'standalone',
            'background_color' => '#0f172a',
            'theme_color'      => '#1e3a8a',
            'orientation'      => 'portrait-primary',
            'icons'            => [
                ['src' => $iconBase . '/livreur-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any maskable'],
                ['src' => $iconBase . '/livreur-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable'],
            ],
        ], 200, ['Content-Type' => 'application/manifest+json']);
    }

    public function updateLocation(Request $request, string $token)
    {
        $livreur = Livreur::where('access_token', $token)->firstOrFail();
        $request->validate([
            'latitude'     => 'required|numeric|between:-90,90',
            'longitude'    => 'required|numeric|between:-180,180',
            'is_available' => 'sometimes|boolean',
        ]);

        $data = ['latitude' => $request->latitude, 'longitude' => $request->longitude];
        if ($request->has('is_available')) {
            $data['is_available'] = $request->boolean('is_available');
        }

        $livreur->update($data);
        return response()->json(['success' => true]);
    }

    public function updateDeliveryStatus(Request $request, string $token, Order $order)
    {
        $livreur = Livreur::where('access_token', $token)->firstOrFail();
        abort_if($order->livreur_id !== $livreur->id, 403);

        $request->validate(['status' => 'required|in:en_route,delivered,cancelled']);

        $oldStatus = $order->status;
        $order->update(['status' => $request->status]);

        // When delivered: free up livreur + deduct stock + create sale
        if ($request->status === 'delivered' && $oldStatus !== 'delivered') {
            $livreur->update(['is_available' => true]);

            $store = $order->store;
            $stock = $store->stock()
                ->where('brand', $order->brand)
                ->where('weight', $order->weight)
                ->first();

            if ($stock && $stock->quantity >= $order->quantity) {
                $stock->decrement('quantity', $order->quantity);
            }

            $client = null;
            if ($order->client_phone) {
                $client = $store->clients()->firstOrCreate(
                    ['phone' => $order->client_phone],
                    ['name'  => $order->client_name]
                );
                $client->increment('total_orders');

                $loyalty = $store->loyaltyProgram;
                if ($loyalty && $loyalty->active) {
                    $client->increment('loyalty_points', intval($order->quantity * $loyalty->points_per_unit));
                }
            }

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

        if ($request->status === 'cancelled') {
            $livreur->update(['is_available' => true]);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'status' => $order->status]);
        }

        return back()->with('success', 'Statut mis à jour.');
    }
}
