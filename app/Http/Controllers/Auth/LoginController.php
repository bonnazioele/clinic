<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Models\Clinic;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/dashboard';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function authenticated(Request $request, $user)
    {
        if ($user->is_admin) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->is_owner) {
            $clinic = Clinic::where('user_id', $user->id)
                ->orderByRaw("CASE WHEN status IN ('approved','active') THEN 0 ELSE 1 END")
                ->orderByDesc('id')
                ->first();
            if ($clinic && $clinic->isApprovedLike()) {
                return redirect()->route('owner.dashboard');
            }
            return redirect()->route('welcome')->with('warning', 'Your clinic application is pending review.');
        }

        if ($user->is_doctor) {
            return redirect()->route('doctor.dashboard');
        }

        if ($user->is_secretary) {
            return redirect()->route('secretary.dashboard');
        }

        return redirect()->route('dashboard');
    }

     public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
