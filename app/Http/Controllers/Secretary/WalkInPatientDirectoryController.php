<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Concerns\InteractsWithClinic;
use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureSelectedClinic;
use App\Models\Patient;
use Illuminate\Http\Request;

class WalkInPatientDirectoryController extends Controller
{
    use InteractsWithClinic;

    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\SecretaryMiddleware::class, EnsureSelectedClinic::class]);
    }

    public function index(Request $request)
    {
        $clinic = $request->attributes->get('active_clinic');
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
}
