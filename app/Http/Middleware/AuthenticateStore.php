<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticateStore
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('store')->check()) {
            $store = Auth::guard('store')->user();
            if ($store->status !== 'active') {
                Auth::guard('store')->logout();
                return redirect()->route('login')->with('error', 'Votre compte est en attente de validation.');
            }
            return $next($request);
        }

        if (Auth::guard('staff')->check()) {
            return $next($request);
        }

        return redirect()->route('login')->with('error', 'Veuillez vous connecter.');
    }
}
