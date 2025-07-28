<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecretaryMiddleware
{
    public function handle(Request $req, Closure $next)
    {
        if (! $req->user()?->is_secretary) {
            abort(403,'Forbidden');
        }
        return $next($req);
    }
}
