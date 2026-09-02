<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Store;
use App\Models\Commissionnaire;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
        if (Auth::guard('commissionnaire')->check()) {
            return redirect()->route('commissionnaire.dashboard');
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

        // Try commissionnaire
        if (Auth::guard('commissionnaire')->attempt($credentials, $request->boolean('remember'))) {
            $commissionnaire = Auth::guard('commissionnaire')->user();
            if ($commissionnaire->status === 'pending') {
                Auth::guard('commissionnaire')->logout();
                return back()->with('error', 'Votre inscription est en attente de validation par l\'administrateur.');
            }
            if ($commissionnaire->status === 'rejected') {
                Auth::guard('commissionnaire')->logout();
                return back()->with('error', 'Votre inscription a été rejetée. Contactez l\'administrateur.');
            }
            $request->session()->regenerate();
            return redirect()->route('commissionnaire.dashboard');
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
            'terms'      => 'accepted',
        ], [
            'store_name.required' => 'Le nom du magasin est requis.',
            'owner_name.required' => 'Le nom du propriétaire est requis.',
            'email.required'      => 'L\'email est requis.',
            'email.unique'        => 'Cet email est déjà utilisé.',
            'phone.required'      => 'Le téléphone est requis.',
            'password.required'   => 'Le mot de passe est requis.',
            'password.min'        => 'Le mot de passe doit avoir au moins 6 caractères.',
            'password.confirmed'  => 'Les mots de passe ne correspondent pas.',
            'terms.accepted'      => 'Vous devez accepter les conditions d\'utilisation.',
        ]);

        $commissionnaire = $request->filled('code_parrain')
            ? Commissionnaire::where('code', $request->code_parrain)->where('status', 'active')->first()
            : null;

        Store::create([
            'store_name'         => $request->store_name,
            'owner_name'         => $request->owner_name,
            'email'              => $request->email,
            'phone'              => $request->phone,
            'password'           => Hash::make($request->password),
            'address'            => $request->address,
            'status'             => 'pending',
            'commissionnaire_id' => $commissionnaire?->id,
        ]);

        return redirect()->route('login')->with('success', 'Inscription réussie ! Votre demande est en attente de validation par l\'administrateur.');
    }

    public function showRegisterCommissionnaire()
    {
        return view('auth.register-commissionnaire');
    }

    public function registerCommissionnaire(Request $request)
    {
        $request->validate([
            'name'             => 'required|string|max:255',
            'email'            => 'required|email|unique:commissionnaires,email',
            'phone'            => 'required|string|max:20',
            'password'         => 'required|string|min:6|confirmed',
            'id_document_type' => 'required|in:cni,passeport',
            'id_document'      => 'required|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'terms'            => 'accepted',
        ], [
            'name.required'             => 'Le nom est requis.',
            'email.required'            => 'L\'email est requis.',
            'email.unique'              => 'Cet email est déjà utilisé.',
            'phone.required'            => 'Le téléphone est requis.',
            'password.required'         => 'Le mot de passe est requis.',
            'password.min'              => 'Le mot de passe doit avoir au moins 6 caractères.',
            'password.confirmed'        => 'Les mots de passe ne correspondent pas.',
            'id_document_type.required' => 'Précisez le type de pièce d\'identité.',
            'id_document.required'      => 'La pièce d\'identité (CNI ou passeport) est requise.',
            'id_document.mimes'         => 'Format accepté : JPG, PNG ou PDF.',
            'id_document.max'           => 'Fichier trop volumineux (4 Mo maximum).',
            'terms.accepted'            => 'Vous devez accepter les conditions d\'utilisation.',
        ]);

        do {
            $code = strtoupper(Str::random(7));
        } while (Commissionnaire::where('code', $code)->exists());

        // Stored on the private (non-public) disk — identity documents are
        // sensitive PII and must not be reachable via a guessable public URL;
        // only admins can view them, via a dedicated authenticated route.
        $documentPath = $request->file('id_document')->store('id-documents', 'local');

        Commissionnaire::create([
            'name'             => $request->name,
            'email'            => $request->email,
            'phone'            => $request->phone,
            'password'         => Hash::make($request->password),
            'code'             => $code,
            'status'           => 'pending',
            'id_document_type' => $request->id_document_type,
            'id_document_path' => $documentPath,
        ]);

        return redirect()->route('login')->with('success', 'Inscription réussie ! Votre demande est en attente de validation par l\'administrateur.');
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        Auth::guard('store')->logout();
        Auth::guard('staff')->logout();
        Auth::guard('commissionnaire')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
