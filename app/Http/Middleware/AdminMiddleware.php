<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()?->isAdmin()) {
            return response()->json([
                'message' => 'دسترسی غیرمجاز. فقط مدیران می‌توانند این عملیات را انجام دهند.',
                'error' => 'forbidden',
            ], 403);
        }

        return $next($request);
    }
}
