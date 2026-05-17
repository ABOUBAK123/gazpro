<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Store;
use App\Models\LoyaltyProgram;

class LoyaltyController extends Controller
{
    private function currentStore(): Store
    {
        return Auth::guard('store')->user();
    }

    public function index()
    {
        $store   = $this->currentStore();
        $loyalty = $store->loyaltyProgram ?? new LoyaltyProgram(['store_id' => $store->id]);
        $clients = $store->clients()->orderByDesc('loyalty_points')->get();
        return view('store.loyalty.index', compact('store', 'loyalty', 'clients'));
    }

    public function update(Request $request)
    {
        $store = $this->currentStore();

        $request->validate([
            'points_per_unit'  => 'required|integer|min:1',
            'reward_threshold' => 'required|integer|min:1',
            'reward_value'     => 'required|numeric|min:0',
            'currency'         => 'required|string|max:10',
        ]);

        $store->loyaltyProgram()->updateOrCreate(
            ['store_id' => $store->id],
            $request->only('points_per_unit', 'reward_threshold', 'reward_value', 'currency') + [
                'active' => $request->boolean('active', true),
            ]
        );

        return back()->with('success', 'Programme de fidélité mis à jour.');
    }
}
