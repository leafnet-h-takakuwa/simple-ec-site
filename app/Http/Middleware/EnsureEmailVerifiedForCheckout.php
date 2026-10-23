<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailVerifiedForCheckout
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // メール未認証会員は認証画面にリダイレクト
        if ($user && !$user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        return $next($request);
    }
}
