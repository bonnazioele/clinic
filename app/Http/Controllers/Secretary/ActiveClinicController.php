<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActiveClinicController extends Controller
{
    public function update(Request $request)
    {
        $user = Auth::user();
        if (! $user || ! $user->is_secretary) {
            abort(403);
        }
        // In-session clinic switching is disabled. To change active clinic, log out and sign in.
        return redirect()->route('secretary.dashboard')
            ->with('status', 'In-session clinic switching is disabled. To change clinic, please log out and sign in.');
    }
}
