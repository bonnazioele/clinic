<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\InteractsWithClinic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClinicSelectionController extends Controller
{
    use InteractsWithClinic;
    public function index(Request $request)
    {
        $user = Auth::user();

        $clinics = $user->secretaryClinics()->orderBy('name')->get(['clinics.id', 'clinics.name']);

        if ($clinics->isEmpty()) {
            abort(403, 'No clinics are assigned to your account.');
        }

        return view('secretary.choose-clinic', [
            'clinics' => $clinics,
            'currentClinicId' => (int) $request->session()->get('active_clinic_id'),
        ]);
    }

    public function select(Request $request)
    {
        $user = Auth::user();

        if (! $user || ! $user->is_secretary) {
            abort(403);
        }

        $data = $request->validate([
            'clinic_id' => ['required', 'integer', 'exists:clinics,id'],
        ]);

        $clinicId = (int) $data['clinic_id'];

        // Security check: Ensure the chosen clinic is actually assigned to this user
        $isAssigned = $user->secretaryClinics()->where('clinics.id', $clinicId)->exists();

        if (!$isAssigned) {
            abort(403, 'Unauthorized clinic selection.');
        }

        $request->session()->put('active_clinic_id', $clinicId);

        return redirect()->route('secretary.dashboard');
    }
}
