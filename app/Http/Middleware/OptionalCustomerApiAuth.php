<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OptionalCustomerApiAuth
{
    public function handle(Request $request, Closure $next)
    {
        $bearer = $request->bearerToken();

        if ($bearer) {
            Auth::shouldUse('customer-api');

            $request->setUserResolver(function () {
                return Auth::guard('customer-api')->user();
            });
        }

        return $next($request);
    }
}
