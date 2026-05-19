<?php

namespace App\Http\Middleware;

use App\Models\AdminList;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (session('emp_data')) {
            $exists = AdminList::where('admin_id', session('emp_data')['emp_id'])->exists();

            if (! $exists) {
                return redirect()->route('dashboard');
            }
        }

        return $next($request);
    }
}
