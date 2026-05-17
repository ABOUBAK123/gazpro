<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Store;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        if (Auth::guard('store')->check() || Auth::guard('staff')->check()) {
            return redirect()->route('store.dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ], [
            'email.required'    => 'L\'email est requis.',
            'email.email'       => 'Email invalide.',
            'password.required' => 'Le mot de passe est requis.',
        ]);

        $credentials = $request->only('email', 'password');

        // Try admin
        if (Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->route('admin.dashboard');
        }

        // Try store (manager)
        if (Auth::guard('store')->attempt($credentials, $request->boolean('remember'))) {
            $store = Auth::guard('store')->user();
            if ($store->status === 'pending') {
                Auth::guard('store')->logout();
                return back()->with('error', 'Votre inscription est en attente de validation par l\'administrateur.');
            }
            if ($store->status === 'rejected') {
                Auth::guard('store')->logout();
                return back()->with('error', 'Votre inscription a été rejetée. Contactez l\'administrateur.');
            }
            $request->session()->regenerate();
            return redirect()->route('store.dashboard');
        }

        // Try staff
        if (Auth::guard('staff')->attempt($credentials, $request->boolean('remember'))) {
            $staff = Auth::guard('staff')->user();
            if ($staff->status !== 'active') {
                Auth::guard('staff')->logout();
                return back()->with('error', 'Votre compte est désactivé.');
            }
            $request->session()->regenerate();
            return redirect()->route('store.dashboard');
        }

        return back()->withErrors(['email' => 'Email ou mot de passe incorrect.'])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'store_name' => 'required|string|max:255',
            'owner_name' => 'required|string|max:255',
            'email'      => 'required|email|unique:stores,email',
            'phone'      => 'required|string|max:20',
            'password'   => 'required|string|min:6|confirmed',
        ], [
            'store_name.required' => 'Le nom du magasin est requis.',
            'owner_name.required' => 'Le nom du propriétaire est requis.',
            'email.required'      => 'L\'email est requis.',
            'email.unique'        => 'Cet email est déjà utilisé.',
            'phone.required'      => 'Le téléphone est requis.',
            'password.required'   => 'Le mot de passe est requis.',
            'password.min'        => 'Le mot de passe doit avoir au moins 6 caractères.',
            'password.confirmed'  => 'Les mots de passe ne correspondent pas.',
        ]);

        Store::create([
            'store_name' => $request->store_name,
            'owner_name' => $request->owner_name,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'password'   => Hash::make($request->password),
            'address'    => $request->address,
            'status'     => 'pending',
        ]);

        return redirect()->route('login')->with('success', 'Inscription réussie ! Votre demande est en attente de validation par l\'administrateur.');
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        Auth::guard('store')->logout();
        Auth::guard('staff')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
