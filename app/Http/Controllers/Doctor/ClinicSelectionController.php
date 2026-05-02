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

        $activeClinicId = (int) $request->session()->get('active_clinic_id');
        if ($activeClinicId > 0 && $clinics->contains('id', $activeClinicId)) {
            return redirect()->route('doctor.dashboard')
                ->with('status', 'To switch clinics, please log out and sign in again.');
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
            'return_to' => ['nullable', 'string', 'max:2048'],
        ]);

        $activeClinicId = (int) $request->session()->get('active_clinic_id');
        $hasActiveClinic = $activeClinicId > 0 && $user->clinics()->where('clinics.id', $activeClinicId)->exists();
        if ($hasActiveClinic) {
            return redirect()->route('doctor.dashboard')
                ->with('status', 'To switch clinics, please log out and sign in again.');
        }

        $clinicId = (int) $data['clinic_id'];

        $isAssigned = $user->clinics()->where('clinics.id', $clinicId)->exists();

        if (! $isAssigned) {
            abort(403, 'Unauthorized clinic selection.');
        }

        $request->session()->put('active_clinic_id', $clinicId);

        $returnTo = $data['return_to'] ?? null;
        if (is_string($returnTo) && str_starts_with($returnTo, url('/'))) {
            return redirect()->to($returnTo)->with('status', 'Active clinic changed.');
        }

        return redirect()->route('doctor.dashboard');
    }
}
