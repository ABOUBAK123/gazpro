<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\MobileUser;

class AuthenticateMobileUser
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        $user  = $token ? MobileUser::where('api_token', $token)->first() : null;

        if (!$user) {
            return response()->json(['message' => 'Non authentifié.'], 401);
        }

        $request->attributes->set('mobileUser', $user);

        return $next($request);
    }
}
