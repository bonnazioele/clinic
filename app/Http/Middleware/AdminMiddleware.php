<?php

// app/Http/Middleware/AdminMiddleware.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $req, Closure $next)
    {
        if (! $req->user()?->is_system_admin) {
            abort(403, 'Forbidden');
        }
        return $next($req);
    }
}

