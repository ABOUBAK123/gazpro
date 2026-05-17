<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Store;
use App\Models\Staff;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search', '');

        $stores = Store::when($search, fn($q) => $q->where(function ($q) use ($search) {
            $q->where('store_name', 'like', "%{$search}%")
              ->orWhere('owner_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        }))->latest()->get();

        $staff = Staff::with('store')->when($search, fn($q) => $q->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhereHas('store', fn($q) => $q->where('store_name', 'like', "%{$search}%"));
        }))->latest()->get();

        return view('admin.accounts', compact('stores', 'staff', 'search'));
    }

    public function updateStore(Request $request, Store $store)
    {
        $request->validate([
            'owner_name' => 'required|string|max:255',
            'email'      => 'required|email|unique:stores,email,' . $store->id,
            'phone'      => 'nullable|string|max:50',
            'status'     => 'required|in:active,pending,rejected',
        ]);

        $store->update($request->only('owner_name', 'email', 'phone', 'status'));

        return back()->with('success', 'Magasin mis à jour.');
    }

    public function destroyStore(Store $store)
    {
        $store->delete();
        return back()->with('success', 'Magasin supprimé.');
    }

    public function updateStaff(Request $request, Staff $staff)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'email'  => 'required|email|unique:staff,email,' . $staff->id,
            'phone'  => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive',
        ]);

        $staff->update($request->only('name', 'email', 'phone', 'status'));

        return back()->with('success', 'Employé mis à jour.');
    }

    public function destroyStaff(Staff $staff)
    {
        $staff->delete();
        return back()->with('success', 'Employé supprimé.');
    }
}
