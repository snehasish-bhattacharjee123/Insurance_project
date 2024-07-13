<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthCheck
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || Auth::user()->user_type != '1') {
            return redirect('/account/login');
        }

        return $next($request);
    }
}
