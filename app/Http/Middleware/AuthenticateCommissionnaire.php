<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticateCommissionnaire
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('commissionnaire')->check()) {
            return redirect()->route('login')->with('error', 'Veuillez vous connecter.');
        }

        $commissionnaire = Auth::guard('commissionnaire')->user();
        if ($commissionnaire->status !== 'active') {
            Auth::guard('commissionnaire')->logout();
            return redirect()->route('login')->with('error', 'Votre compte est en attente de validation.');
        }

        return $next($request);
    }
}
