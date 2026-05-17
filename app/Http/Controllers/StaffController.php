<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Store;
use App\Models\Staff;

class StaffController extends Controller
{
    private function currentStore(): Store
    {
        return Auth::guard('store')->user();
    }

    public function index()
    {
        $store = $this->currentStore();
        $staff = $store->staff()->latest()->get();
        return view('store.staff.index', compact('store', 'staff'));
    }

    public function store(Request $request)
    {
        $store = $this->currentStore();

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:staff,email',
            'phone'    => 'nullable|string|max:20',
            'role'     => 'required|in:employee,cashier,supervisor',
            'password' => 'required|string|min:6',
        ], [
            'name.required'     => 'Le nom est requis.',
            'email.required'    => 'L\'email est requis.',
            'email.unique'      => 'Cet email est déjà utilisé.',
            'role.required'     => 'Le rôle est requis.',
            'password.required' => 'Le mot de passe est requis.',
            'password.min'      => 'Minimum 6 caractères.',
        ]);

        $store->staff()->create([
            'name'     => $request->name,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'role'     => $request->role,
            'password' => Hash::make($request->password),
            'status'   => 'active',
        ]);

        return back()->with('success', 'Employé ajouté avec succès.');
    }

    public function update(Request $request, Staff $staff)
    {
        $store = $this->currentStore();
        abort_if($staff->store_id !== $store->id, 403);

        $request->validate([
            'name'   => 'required|string|max:255',
            'phone'  => 'nullable|string|max:20',
            'role'   => 'required|in:employee,cashier,supervisor',
            'status' => 'required|in:active,inactive',
        ]);

        $data = $request->only('name', 'phone', 'role', 'status');
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $staff->update($data);
        return back()->with('success', 'Employé mis à jour.');
    }

    public function destroy(Staff $staff)
    {
        $store = $this->currentStore();
        abort_if($staff->store_id !== $store->id, 403);
        $staff->delete();
        return back()->with('success', 'Employé supprimé.');
    }
}
