<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\ClinicStatusLog;
use Illuminate\Support\Carbon;

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
        if (Hash::check($request->input('password'), $user->password)) {
            return back()
                ->withErrors(['password' => 'Your new password must be different from your current password.'])
                ->withInput($request->except(['password','password_confirmation']));
        }
        $user->password = Hash::make($request->input('password'));
        $user->is_initial_login = false;
        $user->is_active = true; // Activate account after initial setup completion
        $user->save();

        // Upon first successful password change, activate associated clinics that are approved
        // and create activation logs for audit trail.
        try {
            $secretaryClinics = $user->clinicsAsSecretary()->where('status', 'approved')->get();
            foreach ($secretaryClinics as $clinic) {
                $clinic->update(['status' => 'active']);
                ClinicStatusLog::create([
                    'clinic_id'   => $clinic->id,
                    'action'      => 'activated',
                    'reason'      => 'Activated after first login password change',
                    'performed_by'=> $user->id,
                    'performed_at'=> now(),
                ]);
            }
        } catch (\Throwable $e) {
            // Non-fatal: log activation issues but don't block login
            \Log::error('Clinic activation on first login failed', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
        }

        return redirect()->intended(route('dashboard'))
            ->with('status', 'Password updated successfully. Your account is now active.');
    }
}
