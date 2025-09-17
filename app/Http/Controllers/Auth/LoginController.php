<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users by default.
     */
    protected $redirectTo = '/dashboard';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Override to redirect admins to /admin.
     */
    protected function authenticated(Request $request, $user)
    {
        if ($user->is_admin) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->is_doctor) {
            return redirect()->route('doctor.dashboard');
        }

        if ($user->is_secretary) {
            return redirect()->route('secretary.dashboard');
        }

        // Patients
        return redirect()->route('dashboard');
    }

     public function logout(Request $request)
    {
        // Log out
        Auth::logout();

        // Invalidate their session & regenerate CSRF token
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect them to the login page
        return redirect()->route('login');
    }
}
