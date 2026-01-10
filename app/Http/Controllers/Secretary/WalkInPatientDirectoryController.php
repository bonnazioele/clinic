<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class WalkInPatientDirectoryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (! $user || ! $user->is_secretary) {
            abort(403);
        }

        $clinic = $this->resolveClinic($request);
        if (! $clinic) {
            return redirect()->route('secretary.dashboard')
                ->with('warning', 'Please select an active clinic to view walk-in patients.');
        }

        $search = trim((string) $request->input('q', ''));

        $patientsQuery = Patient::query()
            ->whereHas('visits', fn ($query) => $query->where('clinic_id', $clinic->id))
            ->withCount([
                'visits as clinic_visits_count' => fn ($q) => $q->where('clinic_id', $clinic->id),
            ])
            ->with(['visits' => fn ($q) => $q->where('clinic_id', $clinic->id)->latest('date_of_visit')]);

        if ($search !== '') {
            $patientsQuery->where(function ($query) use ($search) {
                $query->where('patient_number', 'like', "%{$search}%")
                      ->orWhere('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('mobile_number', 'like', "%{$search}%");
            });
        }

        $patients = $patientsQuery
            ->orderBy('last_name')
            ->paginate(12)
            ->withQueryString();

        return view('secretary.walkin.directory', [
            'clinic' => $clinic,
            'patients' => $patients,
            'search' => $search,
        ]);
    }

    protected function resolveClinic(Request $request)
    {
        $user = $request->user();
        $sharedClinic = View::shared('activeClinic');
        if ($sharedClinic) {
            return $sharedClinic;
        }

        $clinicId = (int) $request->session()->get('active_clinic_id');
        if ($clinicId) {
            return $user->secretaryClinics()->where('clinics.id', $clinicId)->first();
        }

        $clinic = $user->secretaryClinics()->orderBy('name')->first();
        if ($clinic) {
            $request->session()->put('active_clinic_id', $clinic->id);
        }

        return $clinic;
    }
}
