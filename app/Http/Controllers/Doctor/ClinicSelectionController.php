<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClinicSelectionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if (! $user || ! $user->is_doctor) {
            abort(403);
        }

        $clinics = $user->clinics()->orderBy('name')->get(['clinics.id', 'clinics.name']);

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

        if (! $user || ! $user->is_doctor) {
            abort(403);
        }

        $data = $request->validate([
            'clinic_id' => ['required', 'integer', 'exists:clinics,id'],
        ]);

        $clinicId = (int) $data['clinic_id'];

        $isAssigned = $user->clinics()->where('clinics.id', $clinicId)->exists();

        if (! $isAssigned) {
            abort(403, 'Unauthorized clinic selection.');
        }

        $request->session()->put('active_clinic_id', $clinicId);

        return redirect()->route('doctor.dashboard');
    }
}
