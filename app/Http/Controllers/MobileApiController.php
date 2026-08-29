<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\AppSetting;
use App\Models\MobileUser;
use App\Models\Store;
use App\Models\Stock;
use App\Models\Order;
use Illuminate\Validation\ValidationException;

class MobileApiController extends Controller
{
    // ─── Auth ───────────────────────────────────────────────────────────────

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'phone'    => 'required|string|max:20|unique:mobile_users,phone',
            'address'  => 'nullable|string|max:255',
            'password' => 'required|string|min:6',
        ]);

        $user  = MobileUser::create($data);
        $token = $user->generateToken();

        return response()->json([
            'token' => $token,
            'user'  => ['id' => $user->id, 'name' => $user->name, 'phone' => $user->phone, 'address' => $user->address],
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone'    => 'required|string',
            'password' => 'required|string',
        ]);

        $user = MobileUser::where('phone', $request->phone)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Numéro ou mot de passe incorrect.'], 401);
        }

        $token = $user->generateToken();

        return response()->json([
            'token' => $token,
            'user'  => ['id' => $user->id, 'name' => $user->name, 'phone' => $user->phone, 'address' => $user->address],
        ]);
    }

    // ─── Stores ─────────────────────────────────────────────────────────────

    public function stores(Request $request)
    {
        $stores = Store::where('status', 'active')
            ->with(['stock' => fn($q) => $q->where('quantity', '>', 0)])
            ->get()
            ->map(fn($store) => $this->formatStore($store));

        return response()->json([
            'data'        => $stores,
            'deliveryFee' => (float) AppSetting::get('delivery_fee', 0),
        ]);
    }

    public function storeDetail(string $id)
    {
        $store = Store::where('status', 'active')
            ->with(['stock' => fn($q) => $q->where('quantity', '>', 0)])
            ->findOrFail($id);

        return response()->json([
            'data'        => $this->formatStore($store),
            'deliveryFee' => (float) AppSetting::get('delivery_fee', 0),
        ]);
    }

    private ?array $brandLogos = null;

    private function brandLogoUrl(string $brand): ?string
    {
        if ($this->brandLogos === null) {
            $raw = AppSetting::get('brands', []);
            $this->brandLogos = [];
            foreach ($raw as $b) {
                $name = is_string($b) ? $b : ($b['name'] ?? '');
                $logo = is_array($b) ? ($b['logo'] ?? null) : null;
                $this->brandLogos[$name] = $logo ? url($logo) : null;
            }
        }
        return $this->brandLogos[$brand] ?? null;
    }

    private function formatStore(Store $store): array
    {
        return [
            'id'        => $store->id,
            'storeName' => $store->store_name,
            'ownerName' => $store->owner_name,
            'phone'     => $store->phone,
            'address'   => $store->address,
            'latitude'  => $store->latitude,
            'longitude' => $store->longitude,
            'status'    => $store->status,
            'products'  => $store->stock->map(fn($s) => [
                'id'           => $s->id,
                'brand'        => $s->brand,
                'weight'       => (string) $s->weight,
                'quantity'     => $s->quantity,
                'unitPrice'    => (float) $s->unit_price,
                'brandLogoUrl' => $this->brandLogoUrl($s->brand),
            ])->values(),
        ];
    }

    // ─── Orders ─────────────────────────────────────────────────────────────

    public function createOrder(Request $request)
    {
        /** @var MobileUser $mobileUser */
        $mobileUser = $request->attributes->get('mobileUser');

        $data = $request->validate([
            'storeId'         => 'required|exists:stores,id',
            'brand'           => 'required|string',
            'weight'          => 'required|string',
            'quantity'        => 'required|integer|min:1',
            'deliveryAddress' => 'required|string',
            'note'            => 'nullable|string',
        ]);

        $stock = Stock::where('store_id', $data['storeId'])
            ->where('brand', $data['brand'])
            ->where('weight', $data['weight'])
            ->first();

        if (!$stock || $stock->quantity < $data['quantity']) {
            throw ValidationException::withMessages(['quantity' => 'Stock insuffisant pour cette commande.']);
        }

        // Price and client identity are always derived server-side (stock
        // price, authenticated mobile user) — never trusted from the request.
        $order = Order::create([
            'store_id'       => $data['storeId'],
            'client_name'    => $mobileUser->name,
            'client_phone'   => $mobileUser->phone,
            'client_address' => $data['deliveryAddress'],
            'brand'          => $data['brand'],
            'weight'         => $data['weight'],
            'quantity'       => $data['quantity'],
            'unit_price'     => $stock->unit_price,
            'total_price'    => $stock->unit_price * $data['quantity'],
            'currency'       => 'XOF',
            'status'         => 'pending',
            'notes'          => $data['note'] ?? null,
        ]);

        return response()->json(['data' => $this->formatOrder($order)], 201);
    }

    public function myOrders(Request $request, string $phone)
    {
        /** @var MobileUser $mobileUser */
        $mobileUser = $request->attributes->get('mobileUser');
        abort_unless($phone === $mobileUser->phone, 403);

        $orders = Order::where('client_phone', $phone)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($o) => $this->formatOrder($o));

        return response()->json(['data' => $orders]);
    }

    private function formatOrder(Order $order): array
    {
        $store = $order->store;
        return [
            'id'              => $order->id,
            'storeId'         => $order->store_id,
            'storeName'       => $store?->store_name ?? '',
            'brand'           => $order->brand,
            'weight'          => (string) $order->weight,
            'quantity'        => $order->quantity,
            'unitPrice'       => (float) $order->unit_price,
            'totalPrice'      => (float) $order->total_price,
            'status'          => $order->status,
            'deliveryAddress' => $order->client_address,
            'clientName'      => $order->client_name,
            'clientPhone'     => $order->client_phone,
            'note'            => $order->notes,
            'createdAt'       => $order->created_at?->toISOString(),
        ];
    }
}
