<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\AppSetting;

class ProfileController extends Controller
{
    private function currentUser()
    {
        return Auth::guard('store')->user() ?? Auth::guard('staff')->user();
    }

    public function index()
    {
        $user = $this->currentUser();
        $isManager = Auth::guard('store')->check();
        return view('store.profile', compact('user', 'isManager'));
    }

    public function settings()
    {
        $user = $this->currentUser();
        $isManager = Auth::guard('store')->check();
        return view('store.profile-settings', compact('user', 'isManager'));
    }

    public function update(Request $request)
    {
        $user = $this->currentUser();
        $isManager = Auth::guard('store')->check();

        $rules = [
            'name'     => 'required|string|max:255',
            'phone'    => 'nullable|string|max:50',
            'password' => 'nullable|string|min:6|confirmed',
        ];

        if ($isManager) {
            $rules['email'] = 'required|email|unique:stores,email,' . $user->id;
        } else {
            $rules['email'] = 'required|email|unique:staff,email,' . $user->id;
        }

        $request->validate($rules);

        $data = ['phone' => $request->phone];
        if ($isManager) {
            $data['owner_name'] = $request->name;
            $data['email'] = $request->email;
        } else {
            $data['name'] = $request->name;
            $data['email'] = $request->email;
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return back()->with('success', 'Profil mis à jour avec succès.');
    }

    public function deliverySettings()
    {
        $store = Auth::guard('store')->user();
        $delivery = AppSetting::get('delivery_' . $store->id, [
            'price'     => 0,
            'threshold' => 0,
        ]);
        return view('store.delivery-settings', compact('store', 'delivery'));
    }

    public function updateDelivery(Request $request)
    {
        $store = Auth::guard('store')->user();

        $request->validate([
            'delivery_price'     => 'required|numeric|min:0',
            'free_threshold'     => 'required|integer|min:0',
        ]);

        AppSetting::set('delivery_' . $store->id, [
            'price'     => $request->delivery_price,
            'threshold' => $request->free_threshold,
        ]);

        return back()->with('success', 'Paramètres de livraison enregistrés.');
    }
}
