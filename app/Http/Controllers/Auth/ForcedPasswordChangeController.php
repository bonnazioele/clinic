<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ForcedPasswordChangeController extends Controller
{
    public function show()
    {
        return view('auth.force-password-change');
    }

    public function update(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();
        // Prevent using the same current (temporary) password
        if (Hash::check($request->input('password'), $user->password)) {
            return back()
                ->withErrors(['password' => 'Your new password must be different from your current password.'])
                ->withInput($request->except(['password','password_confirmation']));
        }
        $user->password = Hash::make($request->input('password'));
        $user->is_initial_login = false;
        $user->save();

        return redirect()->intended(route('dashboard'))
            ->with('status', 'Password updated successfully.');
    }
}
